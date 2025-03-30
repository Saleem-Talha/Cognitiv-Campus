import os
import sys
import json
from dotenv import load_dotenv
import mysql.connector
from openai import OpenAI

load_dotenv()

def get_db_connection():
    try:
        return mysql.connector.connect(
            host=os.getenv('DB_HOST'),
            user=os.getenv('DB_USER'),
            password=os.getenv('DB_PASS'),
            database=os.getenv('DB')
        )
    except mysql.connector.Error as err:
        print(json.dumps({'error': f"Database connection error: {str(err)}"}))
        sys.exit(1)

def save_or_update_ai_response(email, response):
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        
        # First check if entry exists
        check_query = "SELECT id FROM ai_schedules WHERE userEmail = %s"
        cursor.execute(check_query, (email,))
        exists = cursor.fetchone()
        
        if exists:
            # Update existing entry
            update_query = """
            UPDATE ai_schedules 
            SET response = %s, created_at = NOW()
            WHERE userEmail = %s
            """
            cursor.execute(update_query, (response, email))
        else:
            # Insert new entry
            insert_query = """
            INSERT INTO ai_schedules (userEmail, response, created_at)
            VALUES (%s, %s, NOW())
            """
            cursor.execute(insert_query, (email, response))
        
        conn.commit()
        cursor.close()
        conn.close()
    except mysql.connector.Error as err:
        print(f"Database save error: {err}")

def get_user_schedules(email):
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT 
                s.course_id, 
                s.course_type, 
                s.day, 
                s.time,
                CASE 
                    WHEN s.course_type = 'uniCourse' THEN cs.course_name
                    WHEN s.course_type = 'extraCourse' THEN oc.name
                    ELSE 'Unknown Course'
                END AS course_name
            FROM 
                schedules s
            LEFT JOIN 
                course_status cs ON s.course_type = 'uniCourse' AND s.course_id = cs.id
            LEFT JOIN 
                own_course oc ON s.course_type = 'extraCourse' AND s.course_id = oc.id
            WHERE 
                s.user_id = %s
            ORDER BY 
                s.day, s.time
        """, (email,))
        
        schedules = cursor.fetchall()
        cursor.close()
        conn.close()
        
        return schedules
    except mysql.connector.Error as err:
        print(json.dumps({'error': f"Error retrieving schedules: {str(err)}"}))
        sys.exit(1)

def optimize_schedule(schedules, email):
    client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))
    
    schedule_text = "\n".join([
        f"{schedule['course_type'].upper()}: {schedule['course_name']} on {schedule['day']} at {schedule['time']}"
        for schedule in schedules
    ])
    
    prompt = f"""
    Analyze this schedule:
    {schedule_text}
    
    Provide:
    1. Schedule balance assessment
    2. 3 key optimization recommendations
    3. Time management tips (the tips cannot include tips that promote the use of any external tools)
    4. Suggest an optimal schedule by moving course timings or days to increase productivity
    5. Give an estimate on how much % productiviy will increase if the user follows you suggested schedule
    
    Ensure all sections are complete, concise, and actionable.
    """
    
    try:
        for _ in range(3):
            response = client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {"role": "system", "content": "Concisely analyze and optimize the following academic schedule."},
                    {"role": "user", "content": prompt}
                ],
                max_tokens=500,
                temperature=0.7,
                top_p=0.9,
                frequency_penalty=0.2,
                presence_penalty=0.1
            )
            
            ai_response = response.choices[0].message.content.strip()
            
            if all(section in ai_response for section in ["1.", "2.", "3.", "4.", "5."]):
                save_or_update_ai_response(email, ai_response)
                return ai_response
        
        error_response = "Optimization failed after multiple attempts to provide a complete response."
        save_or_update_ai_response(email, error_response)
        return error_response
    
    except Exception as err:
        error_response = f"Optimization error: {str(err)}"
        save_or_update_ai_response(email, error_response)
        return error_response

def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'Email address is required'}))
        sys.exit(1)
    
    email = sys.argv[1]
    
    try:
        schedules = get_user_schedules(email)
        
        if not schedules:
            print(json.dumps({'error': f'No schedules found for email: {email}'}))
            sys.exit(1)
        
        schedule_display = "\n".join([
            f"Course Type: {schedule['course_type'].upper()} - {schedule['course_name']}\n" +
            f"Day: {schedule['day']}, Time: {schedule['time']}\n"
            for schedule in schedules
        ])
        
        optimization = optimize_schedule(schedules, email)
        
        print(json.dumps({
            'schedules': schedule_display,
            'optimization': optimization
        }))
    
    except Exception as err:
        print(json.dumps({'error': f'Unexpected error: {str(err)}'}))
        sys.exit(1)

if __name__ == "__main__":
    main()