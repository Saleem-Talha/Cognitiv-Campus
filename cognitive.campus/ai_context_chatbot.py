import json
import os
import sys
import mysql.connector
from openai import OpenAI
from dotenv import load_dotenv
from datetime import datetime
import re

load_dotenv()

# Initialize OpenAI client
client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))

# Database Connection with connection pooling
db_config = {
    'host': os.getenv("DB_HOST"),
    'user': os.getenv("DB_USER"),
    'password': os.getenv("DB_PASSWORD"),
    'database': os.getenv("DB"),
    'pool_name': 'cognitive_pool',
    'pool_size': 5
}

# Function to convert datetime objects into strings
def serialize_data(data):
    if isinstance(data, list):
        return [serialize_data(item) for item in data]
    elif isinstance(data, dict):
        return {key: (value.isoformat() if isinstance(value, datetime) else serialize_data(value)) 
                for key, value in data.items()}
    return data

# Load and prepare context JSON - cached to avoid repeated file operations
_context_cache = None
def get_context():
    global _context_cache
    if _context_cache is None:
        with open('ai_website_context.json', 'r') as file:
            _context_cache = json.load(file)
    return _context_cache

# Extract only needed context sections based on query
def get_relevant_context(user_message):
    context = get_context()
    relevant_sections = {}
    
    # Always include platform overview
    relevant_sections["platform_overview"] = context.get("platform_overview", {})
    relevant_sections["core_features"] = context.get("core_features", [])
    
    # Check for specific topics in the user's message
    message_lower = user_message.lower()
    
    # Map keywords to context sections
    keyword_mapping = {
        "auth": "auth_system",
        "login": "auth_system", 
        "register": "auth_system",
        "password": "auth_system",
        "dashboard": "dashboard",
        "course": "courses_management",
        "subject": "courses_management",
        "project": "projects",
        "note": "notes_system",
        "ai": "llm_assistant", 
        "assistant": "llm_assistant",
        "cognitive assistant": "llm_assistant",
        "schedule": "schedules",
        "notification": "notifications",
        "profile": "user_profile",
        "search": "search_functionality",
        "analytics": "student_analytics",
        "feedback": "feedback_system",
        "request": "requests_page",
        "price": "pricing_plans",
        "pricing": "pricing_plans",
        "subscription": "pricing_plans",
        "plan": "pricing_plans",
        "faq": "faq",
        "developers":"developer_team"
    }
    
    # Add relevant sections based on keywords
    for keyword, section in keyword_mapping.items():
        if keyword in message_lower and section in context:
            relevant_sections[section] = context[section]
    
    # If no specific sections matched, provide general information
    if len(relevant_sections) <= 2:  # Only platform_overview and core_features
        relevant_sections["faq"] = context.get("faq", [])
        
    return relevant_sections

# Fetch user-specific data - with focus on courses and projects
def fetch_user_data(user_email):
    try:
        connection = mysql.connector.connect(**db_config)
        cursor = connection.cursor(dictionary=True)
        user_data = {}

        # Fetch user details - only essential fields
        cursor.execute("SELECT id, name, plan FROM users WHERE email = %s", (user_email,))
        user = cursor.fetchone()

        if not user:
            return {"error": "User not found"}

        user_id = user["id"]
        user["email"] = user_email  
        user_data["user"] = user

        # Fetch ALL course status - essential fields only
        cursor.execute(
            "SELECT course_name FROM course_status WHERE user_id = %s", 
            (user_email,)
        )
        user_data["course_status"] = cursor.fetchall()

        # Fetch ALL own courses - essential info only
        cursor.execute(
            "SELECT name FROM own_course WHERE userEmail = %s", 
            (user_email,)
        )
        user_data["own_course"] = cursor.fetchall()

        # Fetch ALL projects - essential fields only
        cursor.execute(
            "SELECT id, name, status FROM projects WHERE ownerEmail = %s", 
            (user_email,)
        )
        user_data["projects"] = cursor.fetchall()

        return serialize_data(user_data)

    except Exception as e:
        print(f"Database Error: {e}")
        return {"error": f"Database error: {str(e)}"}
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'connection' in locals():
            connection.close()

# Generate AI response with reduced token usage
def get_response(user_message, user_email, start_new_chat=False):
    try:
        # Check if this is a general question about the platform
        is_general_question = re.search(r'\b(what|how|explain|tell me about|describe)\b.*\b(platform|cognitive campus|system)\b', user_message.lower())
        
        # Get relevant sections from context based on query
        relevant_context = get_relevant_context(user_message)
        
        # Only fetch user data if it's likely to be a user-specific question
        user_data = {"error": "Not fetched"} 
        if not is_general_question or re.search(r'\b(my|mine|I|me)\b', user_message.lower()):
            user_data = fetch_user_data(user_email)

        if "error" in user_data and user_data["error"] != "Not fetched":
            return user_data["error"]

        # Extract platform information
        platform_info = relevant_context.get("platform_overview", {})
        
        # Create focused system prompt - dynamic based on the query type
        system_prompt = f"""You are a Cognitive Campus assistant."""
        
        # Add relevant platform info
        system_prompt += f"""

GENERAL PLATFORM INFORMATION (Use this to answer general questions about the platform):
- Platform Name: {platform_info.get("name", "Cognitive Campus")}
- Tagline: {platform_info.get("tagline", "")}
- Mission: {platform_info.get("mission", "")}
- Core Features: {", ".join(relevant_context.get("core_features", []))}
"""

        # Add specialized sections based on query relevance
        for section_name, section_data in relevant_context.items():
            if section_name not in ["platform_overview", "core_features"]:
                system_prompt += f"\n{section_name.upper().replace('_', ' ')}:\n"
                if isinstance(section_data, list):
                    if section_name == "faq":
                        for item in section_data:
                            system_prompt += f"Q: {item.get('question', '')}\n"
                            system_prompt += f"A: {item.get('answer', '')}\n\n"
                    else:
                        system_prompt += json.dumps(section_data, indent=1)
                else:
                    system_prompt += json.dumps(section_data, indent=1)
                system_prompt += "\n"
        
        # Add user-specific data if available
        if "error" not in user_data or user_data["error"] == "Not fetched":
            if "error" not in user_data:
                system_prompt += f"""
USER-SPECIFIC INFORMATION (Only use this to answer questions about this specific user):
- User Name: {user_data.get("user", {}).get("name", "")}
- User Email: {user_data.get("user", {}).get("email", "")}
- Subscription Plan: {user_data.get("user", {}).get("plan", "")}

IMPORTANT TERMINOLOGY:
- When the user refers to "uni courses" or "gcr courses", they are referring to items in the course_status list.
- When the user refers to "extra courses" or "own courses", they are referring to items in the own_course list.
- When the user refers to "name" or "username", they are referring to items in the users list.
- When the user refers to "subscription plan" or "plan", they are referring to plan in the users list.
"""

                if "course_status" in user_data:
                    system_prompt += f"""
University/GCR Courses ({len(user_data["course_status"])}):
{json.dumps([{"name": course["course_name"]} for course in user_data["course_status"]], indent=1)}
"""

                if "own_course" in user_data:
                    system_prompt += f"""
Extra/Own Courses ({len(user_data["own_course"])}):
{json.dumps([{"name": course["name"]} for course in user_data["own_course"]], indent=1)}
"""

                if "projects" in user_data:
                    system_prompt += f"""
Projects ({len(user_data["projects"])}):
{json.dumps([{"name": project["name"], "status": project["status"]} for project in user_data["projects"]], indent=1)}
"""

        system_prompt += """
IMPORTANT INSTRUCTIONS:
1. For general questions about the platform, features, or how things work, ONLY use the General Platform Information section.
2. For user-specific questions about their courses, projects, or account, use the User-Specific Information section.
3. Do not mix these contexts - keep general platform info separate from user-specific details.
4. Be concise and direct in your responses.
5. If you don't have specific information to answer a question, say so rather than making up information.
"""

        # Call OpenAI API with focused context - consider upgrading to GPT-4 for better understanding
        response = client.chat.completions.create(
            model="gpt-3.5-turbo",  # Consider using gpt-4 for more complex queries
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_message}
            ]
        )

        return response.choices[0].message.content.strip()

    except Exception as e:
        return f"An error occurred: {str(e)}"

# Main execution
if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python chatbot.py '<user_message>' '<user_email>'")
    else:
        user_message = sys.argv[1]
        user_email = sys.argv[2]
        print(get_response(user_message, user_email))