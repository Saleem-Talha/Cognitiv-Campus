import sys
import json
import os
import mysql.connector
from dotenv import load_dotenv
from openai import OpenAI
from datetime import datetime, time


load_dotenv()

# Initialize database connection
def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv('DB_HOST'),
        user=os.getenv('DB_USER'),
        password=os.getenv('DB_PASS'),
        database=os.getenv('DB')
    )

# Create user if not exists
def create_user_if_not_exists(email, plan='basic'):
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    try:
        # Check if user exists
        cursor.execute("SELECT id FROM users WHERE email = %s", (email,))
        user = cursor.fetchone()
        if not user:
            # Create new user
            cursor.execute("INSERT INTO users (email, plan) VALUES (%s, %s)", (email, plan))
            conn.commit()
            user_id = cursor.lastrowid
        else:
            user_id = user['id']
        return user_id
    finally:
        cursor.close()
        conn.close()

def save_session_with_title(user_id, title, is_active=1):
    """
    Save a new chat session with the given title and user_id
    
    Args:
        user_id (int): The ID of the user
        title (str): The generated title for the session
        is_active (int): Whether the session is active (default: 1)
    
    Returns:
        int: The ID of the newly created session
    """
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("""
            INSERT INTO ai_chat_sessions (user_id, title, is_active, chat_count, last_reset) 
            VALUES (%s, %s, %s, 0, NULL)
        """, (user_id, title, is_active))
        conn.commit()
        return cursor.lastrowid
    finally:
        cursor.close()
        conn.close()

def get_or_create_session(user_id, title=None):
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    try:
        # Check if there is an ongoing session
        cursor.execute("""
            SELECT id 
            FROM ai_chat_sessions 
            WHERE user_id = %s AND is_active = 1
        """, (user_id,))
        session = cursor.fetchone()

        if session:
            return session['id']

        # If no active session, create a new one
        if not title:
            title = "Untitled Chat"
        cursor.execute("""
            INSERT INTO ai_chat_sessions (user_id, title, is_active, chat_count, last_reset) 
            VALUES (%s, %s, 1, 0, NULL)
        """, (user_id, title))
        conn.commit()
        return cursor.lastrowid
    finally:
        cursor.close()
        conn.close()

def close_current_session(user_id):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("""
            UPDATE ai_chat_sessions 
            SET is_active = 0 
            WHERE user_id = %s AND is_active = 1
        """, (user_id,))
        conn.commit()
    finally:
        cursor.close()
        conn.close()

def save_chat_message(session_id, sender, message):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("""
            INSERT INTO ai_chat_messages (session_id, sender, message) 
            VALUES (%s, %s, %s)
        """, (session_id, sender, message))
        conn.commit()
    finally:
        cursor.close()
        conn.close()



def generate_meaningful_title(session_id, client=None):
    """
    Generate a title based on current date-time and session number for the day
    """
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        
        # Get current date
        current_date = datetime.now().strftime("%Y-%m-%d")
        
        # Count sessions for the current day
        cursor.execute("""
            SELECT COUNT(*) as session_count 
            FROM ai_chat_sessions 
            WHERE DATE(created_at) = CURDATE()
        """)
        session_count = cursor.fetchone()[0]
        
        # Create title with date and sequential number
        title = f"{current_date}-{session_count + 1}"
        
        # Update the session title
        cursor.execute("""
            UPDATE ai_chat_sessions 
            SET title = %s 
            WHERE id = %s
        """, (title, session_id))
        conn.commit()
        
        return title
    
    except Exception as e:
        print(f"Error generating title: {e}")
        return f"{datetime.now().strftime('%Y-%m-%d')}-1"
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'conn' in locals():
            conn.close()



def check_and_reset_chat_counts():
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        # Get current time
        current_time = datetime.now()
        reset_time = time(00, 00)  # Midnight
        current_time_only = current_time.time()
        
        # If current time is past midnight
        if current_time_only >= reset_time:
            # Reset counts for sessions that haven't been reset today or were last reset before midnight today
            cursor.execute("""
                UPDATE ai_chat_sessions 
                SET chat_count = 0,
                    last_reset = CURRENT_TIMESTAMP
                WHERE DATE(last_reset) < CURDATE() 
                   OR last_reset IS NULL 
                   OR (DATE(last_reset) = CURDATE() AND TIME(last_reset) < %s)
            """, (reset_time.strftime('%H:%M:%S'),))
            conn.commit()
    finally:
        cursor.close()
        conn.close()

def get_session_messages(session_id):
    """
    Fetch all messages from the database for a given session ID.
    """
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    try:
        cursor.execute("""
            SELECT sender, message
            FROM ai_chat_messages
            WHERE session_id = %s
            ORDER BY id ASC
        """, (session_id,))
        messages = cursor.fetchall()
        # Format messages for OpenAI API
        formatted_messages = [{"role": "user" if msg['sender'] == 'user' else "assistant", "content": msg['message']} for msg in messages]
        return formatted_messages
    finally:
        cursor.close()
        conn.close()

def get_response(user_input, user_email, start_new_chat=False):
    """
    Generate AI response with enhanced handling and validation
    
    Args:
        user_input (str): User's message
        user_email (str): User's email address
        start_new_chat (bool): Flag to start a new chat session
    
    Returns:
        str: Complete AI response
    """
    try:
        # Check and reset chat counts if needed
        check_and_reset_chat_counts()
        
        # Create user if not exists
        user_id = create_user_if_not_exists(user_email)
        
        # Initialize OpenAI client
        client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))
        
        # Handle session
        if start_new_chat:
            close_current_session(user_id)
            
            # Create a temporary session to store the first message
            session_id = save_session_with_title(user_id, "New Chat")
            
            # Save the first message
            save_chat_message(session_id, 'user', user_input)
            
            # Generate title after we have the first message
            session_title = generate_meaningful_title(session_id, client)
            
            # Update the session title
            conn = get_db_connection()
            cursor = conn.cursor()
            try:
                cursor.execute("""
                    UPDATE ai_chat_sessions 
                    SET title = %s 
                    WHERE id = %s
                """, (session_title, session_id))
                conn.commit()
            finally:
                cursor.close()
                conn.close()
        else:
            session_id = get_or_create_session(user_id)

        # Check and increment chat count
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        try:
            cursor.execute("""
                SELECT cs.chat_count, u.plan 
                FROM ai_chat_sessions cs 
                JOIN users u ON cs.user_id = u.id 
                WHERE cs.id = %s
            """, (session_id,))
            session_info = cursor.fetchone()

            if session_info:
                chat_count = session_info['chat_count']
                plan = session_info['plan']

                # Plan-based chat limit checks
                if plan == 'basic' and chat_count >= 3:
                    return "You have reached the chat limit for basic plan."
                if plan == 'standard' and chat_count >= 25:
                    return "You have reached the chat limit for standard plan."
                if plan == 'pro' and chat_count >= 70:
                    return "You have reached the chat limit for your current plan."

                # Increment chat count
                cursor.execute("""
                    UPDATE ai_chat_sessions 
                    SET chat_count = chat_count + 1 
                    WHERE id = %s
                """, (session_id,))
                conn.commit()
        finally:
            cursor.close()
            conn.close()

        # Save user message if not starting a new chat
        if not start_new_chat:
            save_chat_message(session_id, 'user', user_input)

        # Fetch conversation history
        conversation_history = get_session_messages(session_id)
        
        # Enhanced AI response generation
        try:
            response = client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=conversation_history,
                max_tokens=300,  # Increased token limit
                temperature=0.7,
                top_p=0.9,
                frequency_penalty=0.0,
                presence_penalty=0.0,
                stream=False  # Ensure full response
            )
            
            # Extract AI response
            ai_response = response.choices[0].message.content.strip()
            
            # Response validation
            if not ai_response:
                ai_response = "I apologize, but I couldn't generate a meaningful response. Could you please rephrase or provide more context?"
            
            # Minimum response length check
            if len(ai_response) < 20:
                ai_response = "I'm having difficulty providing a comprehensive response. Could you elaborate on your request or provide additional details?"
            
            # Maximum response length trimming (optional)
            if len(ai_response) > 1000:
                ai_response = ai_response[:1000] + "... [Response truncated]"
            
            # Save the processed AI response
            save_chat_message(session_id, 'ai', ai_response)
            
            return ai_response
        
        except Exception as openai_error:
            # Detailed OpenAI error handling
            error_message = f"OpenAI API Error: {str(openai_error)}"
            print(error_message)  # Log for debugging
            
            # Save error message
            save_chat_message(session_id, 'ai', error_message)
            
            return "Sorry, there was an issue generating a response. Please try again later."
    
    except Exception as general_error:
        # Catch-all error handling
        error_message = f"Unexpected error: {str(general_error)}"
        print(error_message)  # Log for debugging
        
        return "An unexpected error occurred. Our team has been notified. Please try again."

# Main entry point
if __name__ == "__main__":
    if len(sys.argv) > 3:
        user_message = sys.argv[1]
        user_email = sys.argv[2]
        start_new_chat = sys.argv[3].lower() == 'true'
        response = get_response(user_message, user_email, start_new_chat)
        print(response.strip())