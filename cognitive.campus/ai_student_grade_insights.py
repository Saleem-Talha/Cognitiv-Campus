import os
import sys
import mysql.connector
from dotenv import load_dotenv
from openai import OpenAI
from datetime import datetime


load_dotenv()

# Initialize OpenAI client
client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))

# Initialize database connection
def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv('DB_HOST'),
        user=os.getenv('DB_USER'),
        password=os.getenv('DB_PASS'),
        database=os.getenv('DB')
    )

def create_insights_table_if_not_exists():
    """Create the table to store AI insights if it doesn't exist"""
    conn = get_db_connection()
    cursor = conn.cursor()
    
    cursor.execute('''
    CREATE TABLE IF NOT EXISTS student_grade_insights (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(255) NOT NULL,
        user_email VARCHAR(255) NOT NULL,
        insight_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES student_grades(user_id)
    )
    ''')
    
    conn.commit()
    cursor.close()
    conn.close()

def get_student_grades(user_email):
    """Retrieve all grades for a specific student by email"""
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    
    query = '''
    SELECT 
        user_id,
        course_id,
        course_name,
        assignment_id,
        assignment_title,
        assignment_type,
        grade,
        max_points,
        submission_state,
        submitted_at,
        graded_at
    FROM 
        student_grades
    WHERE 
        user_id = %s
    ORDER BY 
        course_name, assignment_title
    '''
    
    cursor.execute(query, (user_email,))
    grades = cursor.fetchall()
    
    cursor.close()
    conn.close()
    
    return grades

def generate_ai_insights(grades, user_email):
    """Generate insights using OpenAI based on student grades"""
    if not grades:
        return "No grades found for this student."
    
    # Prepare data for OpenAI
    courses = {}
    for grade in grades:
        course_name = grade['course_name']
        if course_name not in courses:
            courses[course_name] = []
        
        # Safely handle NULL/None values for grade and max_points
        grade_value = grade['grade'] if grade['grade'] is not None else 0
        max_points_value = grade['max_points'] if grade['max_points'] is not None else 1
        
        # Calculate percentage - avoid division by zero
        if max_points_value > 0:
            percentage = (grade_value / max_points_value) * 100
        else:
            percentage = 0
        
        # Format grade information
        grade_status = f"{grade_value}/{max_points_value}"
        if grade['grade'] is None:
            grade_status = "Not graded yet"
        
        courses[course_name].append({
            'assignment_title': grade['assignment_title'],
            'assignment_type': grade['assignment_type'],
            'grade_status': grade_status,
            'percentage': percentage,
            'submission_state': grade['submission_state'] or "Unknown"
        })
    
    # Create a structured prompt for OpenAI
    prompt = f"""
    I need a detailed analysis for a student with email {user_email}. 
    Here are their grades across different courses:
    
    """
    
    for course, assignments in courses.items():
        prompt += f"\nCourse: {course}\n"
        for assignment in assignments:
            prompt += f"- {assignment['assignment_title']} ({assignment['assignment_type']}): "
            prompt += f"{assignment['grade_status']} "
            
            if "Not graded" not in assignment['grade_status']:
                prompt += f"({assignment['percentage']:.1f}%) "
            
            prompt += f"- {assignment['submission_state']}\n"
    
    prompt += """
    Based on these grades, please provide:
    1. An overall assessment of the student's performance
    2. Areas of strength and weakness identified from the grades
    3. Specific suggestions for improvement in each course
    4. Learning strategies tailored to the student's performance pattern
    5. Prioritized action items for the student to improve their academic performance
    
    Make the feedback constructive, actionable, and personalized.
    """
    
    # Call OpenAI API
    response = client.chat.completions.create(
        model="gpt-3.5-turbo",  
        messages=[
            {"role": "system", "content": "You are an educational analytics expert specializing in providing personalized academic insights and improvement strategies."},
            {"role": "user", "content": prompt}
        ],
        max_tokens=1500
    )
    
    return response.choices[0].message.content

def save_insights_to_db(user_email, insights):
    """Save the AI-generated insights to the database"""
    conn = get_db_connection()
    cursor = conn.cursor()
    
    query = '''
    INSERT INTO student_grade_insights
        (user_id, user_email, insight_text)
    VALUES
        (%s, %s, %s)
    '''
    
    cursor.execute(query, (user_email, user_email, insights))
    conn.commit()
    
    cursor.close()
    conn.close()

def main(target_email):
    """Main function to process a single student's grades"""
    print(f"Processing grades for student: {target_email}")
    
    # Ensure the insights table exists
    create_insights_table_if_not_exists()
    
    # Get student grades
    grades = get_student_grades(target_email)
    
    if not grades:
        print(f"No grades found for {target_email}")
        return
    
    print(f"Found {len(grades)} grade entries")
    
    # Generate insights
    insights = generate_ai_insights(grades, target_email)
    
    # Save insights
    save_insights_to_db(target_email, insights)
    
    print(f"Insights generated and saved for {target_email}")
    print("\nPreview of insights:")
    print(insights[:300] + "..." if len(insights) > 300 else insights)

if len(sys.argv) > 1:
    student_email = sys.argv[1]
else:
    sys.exit("No email provided")

load_dotenv()

if __name__ == "__main__":
    main(student_email)