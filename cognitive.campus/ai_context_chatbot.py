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

# Improved function to extract relevant context sections based on query
def get_relevant_context(user_message):
    context = get_context()
    relevant_sections = {}
    
    # Always include platform overview for context
    relevant_sections["platform_overview"] = context.get("platform_overview", {})
    relevant_sections["core_features"] = context.get("core_features", [])
    
    # Process user message for better matching
    message_lower = user_message.lower()
    
    # Check for count-related questions about user items
    if re.search(r'\b(how many|count|number of|total)\b.*\b(courses?|projects?|notes?|todos?|tasks?)\b', message_lower):
        # We'll handle this with user data instead of context data
        # Just ensure we have some basic platform info
        return relevant_sections
    
    # Map keywords to context sections with expanded synonyms and related terms
    keyword_mapping = {
        # Auth related
        "auth": "auth_system",
        "login": "auth_system", 
        "register": "auth_system",
        "sign up": "auth_system",
        "sign in": "auth_system",
        "password": "auth_system",
        "account": "auth_system",
        "credentials": "auth_system",
        "authentication": "auth_system",
        "forgot password": "auth_system",
        "reset password": "auth_system",
        
        # Dashboard related
        "dashboard": "dashboard",
        "home page": "dashboard",
        "main screen": "dashboard",
        "landing page": "dashboard",
        "overview": "dashboard",
        
        # Courses related
        "course": "courses_management",
        "subject": "courses_management",
        "class": "courses_management",
        "lecture": "courses_management",
        "study": "courses_management",
        "learning": "courses_management",
        "tutorial": "courses_management",
        "curriculum": "courses_management",
        "module": "courses_management",
        "lesson": "courses_management",
        "uni course": "courses_management",
        "gcr course": "courses_management",
        
        # Projects related
        "project": "projects",
        "assignment": "projects",
        "task": "projects",
        "work": "projects",
        "portfolio": "projects",
        "collaboration": "projects",
        
        # Notes related
        "note": "notes_system",
        "memo": "notes_system",
        "annotation": "notes_system",
        "jot down": "notes_system",
        "write down": "notes_system",
        "reminder": "notes_system",
        
        # AI related
        "ai": "llm_assistant", 
        "assistant": "llm_assistant",
        "cognitive assistant": "llm_assistant",
        "bot": "llm_assistant",
        "chatbot": "llm_assistant",
        "artificial intelligence": "llm_assistant",
        "help": "llm_assistant",
        
        # Schedule related
        "schedule": "schedules",
        "timetable": "schedules",
        "calendar": "schedules",
        "planning": "schedules",
        "agenda": "schedules",
        "event": "schedules",
        "time": "schedules",
        "date": "schedules",
        
        # Notifications related
        "notification": "notifications",
        "alert": "notifications",
        "reminder": "notifications",
        "message": "notifications",
        "update": "notifications",
        "inform": "notifications",
        
        # User profile related
        "profile": "user_profile",
        "account": "user_profile",
        "personal info": "user_profile",
        "details": "user_profile",
        "settings": "user_profile",
        "preferences": "user_profile",
        "avatar": "user_profile",
        
        # Search related
        "search": "search_functionality",
        "find": "search_functionality",
        "lookup": "search_functionality",
        "discover": "search_functionality",
        "locate": "search_functionality",
        "query": "search_functionality",
        
        # Analytics related
        "analytics": "student_analytics",
        "statistics": "student_analytics",
        "data": "student_analytics",
        "metrics": "student_analytics",
        "performance": "student_analytics",
        "progress": "student_analytics",
        "report": "student_analytics",
        "tracking": "student_analytics",
        
        # Feedback related
        "feedback": "feedback_system",
        "review": "feedback_system",
        "opinion": "feedback_system",
        "comment": "feedback_system",
        "suggestion": "feedback_system",
        "evaluation": "feedback_system",
        "rating": "feedback_system",
        
        # Requests related
        "request": "requests_page",
        "ask for": "requests_page",
        "propose": "requests_page",
        "inquiry": "requests_page",
        "application": "requests_page",
        "submit": "requests_page",
        
        # Pricing related
        "price": "pricing_plans",
        "pricing": "pricing_plans",
        "subscription": "pricing_plans",
        "plan": "pricing_plans",
        "payment": "pricing_plans",
        "cost": "pricing_plans",
        "fee": "pricing_plans",
        "charge": "pricing_plans",
        "billing": "pricing_plans",
        "premium": "pricing_plans",
        "free": "pricing_plans",
        "upgrade": "pricing_plans",
        
        # FAQ related
        "faq": "faq",
        "question": "faq",
        "help": "faq",
        "support": "faq",
        "common question": "faq",
        "frequently asked": "faq",
        "how to": "faq",
        
        # Developer related
        "developers": "developer_team",
        "team": "developer_team",
        "creator": "developer_team",
        "built by": "developer_team",
        "engineering": "developer_team",
        "programmers": "developer_team",
        "staff": "developer_team",
        "contact": "developer_team"
    }
    
    # Check if any keyword is present in the user message
    matched_sections = set()
    for keyword, section in keyword_mapping.items():
        if keyword in message_lower:
            matched_sections.add(section)
    
    # Add all matched sections to the relevant sections
    for section in matched_sections:
        if section in context:
            relevant_sections[section] = context[section]
    
    # If no specific sections matched (beyond platform overview and core features),
    # check for conceptual similarity using key phrases
    if len(relevant_sections) <= 2:
        # Check for intent patterns
        intent_patterns = {
            r"how (do|can|does|to) .*(use|work|function)": ["dashboard", "faq"],
            r"what (is|are) .*(feature|available|offer)": ["core_features", "faq"],
            r"(help|support|assistance)": ["faq", "llm_assistant"],
            r"(problem|issue|error|trouble)": ["faq", "feedback_system"],
            r"(create|make|start|new)": ["courses_management", "projects", "notes_system"],
            r"(my|account|profile)": ["user_profile", "auth_system"],
            r"(cost|pay|price|money)": ["pricing_plans"],
            r"(learn|study|education)": ["courses_management", "student_analytics"]
        }
        
        for pattern, sections in intent_patterns.items():
            if re.search(pattern, message_lower):
                for section in sections:
                    if section in context and section not in relevant_sections:
                        relevant_sections[section] = context[section]
        
        # If still no match or very minimal matches, add FAQ as fallback
        if len(relevant_sections) <= 2:
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
        
        # Fetch ALL notes - essential fields only
        cursor.execute(
            "SELECT id, page_title FROM notes_course WHERE userEmail = %s", 
            (user_email,)
        )
        user_data["notes_course"] = cursor.fetchall()
        # Fetch ALL notes - essential fields only
        cursor.execute(
            "SELECT id, page_title FROM notes_project WHERE userEmail = %s", 
            (user_email,)
        )
        user_data["notes_project"] = cursor.fetchall()
        
        # Fetch ALL todos/tasks - essential fields only
        cursor.execute(
            "SELECT id, title FROM tasks WHERE userEmail = %s", 
            (user_email,)
        )
        user_data["todos"] = cursor.fetchall()
        
        # Add count summaries for easy reference
        user_data["counts"] = {
            "uni_courses": len(user_data["course_status"]),
            "extra_courses": len(user_data["own_course"]),
            "projects": len(user_data["projects"]),
            "course_notes": len(user_data["notes_course"]) if "notes" in user_data else 0,
            "projects_notes": len(user_data["notes_project"]) if "notes" in user_data else 0,
            "todos": len(user_data["todos"]) if "todos" in user_data else 0
        }

        return serialize_data(user_data)

    except Exception as e:
        print(f"Database Error: {e}")
        return {"error": f"Database error: {str(e)}"}
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'connection' in locals():
            connection.close()

# Generate AI response with improved context handling
def get_response(user_message, user_email, start_new_chat=False):
    try:
        # Check if this is a general question about the platform or a specific user question
        is_general_question = not bool(re.search(r'\b(my|mine|I|me)\b', user_message.lower()))
        is_platform_question = bool(re.search(r'\b(what|how|explain|tell me about|describe|platform|cognitive campus|system)\b', user_message.lower()))
        
        # Get relevant sections from context based on query
        relevant_context = get_relevant_context(user_message)
        
        # Only fetch user data if it's likely to be a user-specific question
        user_data = {"error": "Not fetched"} 
        if not is_general_question or not is_platform_question:
            user_data = fetch_user_data(user_email)

        if "error" in user_data and user_data["error"] != "Not fetched":
            return user_data["error"]

        # Extract platform information
        platform_info = relevant_context.get("platform_overview", {})
        
        # Create focused system prompt - dynamic based on the query type
        system_prompt = f"""You are a Cognitive Campus assistant - the intelligent virtual assistant for our educational platform.

RESPONSE GUIDELINES:
1. Provide helpful, accurate information about the Cognitive Campus platform and its features.
2. For user-specific questions, use their personal data to provide personalized responses.
3. Keep responses concise, friendly, and focused on answering the question.
4. If the query is completely unrelated to our platform, educational technology, or the user's data, respond with: "I'm sorry, I can only provide information about the Cognitive Campus platform and its features. Please ask something related to our educational services."
5. Attempt to interpret user questions flexibly - find the most relevant information even if the query doesn't exactly match our predefined categories.
"""
        
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

USER ITEM COUNTS:
- University/GCR Courses: {user_data.get("counts", {}).get("uni_courses", 0)}
- Extra/Own Courses: {user_data.get("counts", {}).get("extra_courses", 0)}
- Projects: {user_data.get("counts", {}).get("projects", 0)}
- Notes: {user_data.get("counts", {}).get("notes", 0)}
- Todos/Tasks: {user_data.get("counts", {}).get("todos", 0)}

IMPORTANT TERMINOLOGY:
- When the user refers to "uni courses" or "gcr courses", they are referring to items in the course_status list.
- When the user refers to "extra courses" or "own courses", they are referring to items in the own_course list.
- When the user refers to "name" or "username", they are referring to items in the users list.
- When the user refers to "subscription plan" or "plan", they are referring to plan in the users list.
- When the user asks about "how many" items they have, refer to the USER ITEM COUNTS section.

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
                    
                    # Add Notes data if available

                if "course_notes" in user_data:
                    system_prompt += f"""
Course Notes ({len(user_data["notes_course"])}):
{json.dumps([{"title": note["title"]} for note in user_data["notes_course"]], indent=1)}
"""
                if "project_notes" in user_data:
                    system_prompt += f"""
Project Notes ({len(user_data["notes_project"])}):
{json.dumps([{"title": note["title"]} for note in user_data["notes_project"]], indent=1)}
"""

                # Add Todos/Tasks data if available
                if "todos" in user_data:
                    system_prompt += f"""
Todos/Tasks ({len(user_data["todos"])}):
{json.dumps([{"title": todo["title"]} for todo in user_data["todos"]], indent=1)}
"""


        system_prompt += """
IMPORTANT INSTRUCTIONS:
1. For general questions about the platform, features, or how things work, ONLY use the General Platform Information section.
2. For user-specific questions about their courses, projects, or account, use the User-Specific Information section.
3. Do not mix these contexts - keep general platform info separate from user-specific details.
4. Be concise and direct in your responses.
5. If you don't have specific information to answer a question, say so rather than making up information.
6. Always try to provide a helpful response, even if the query doesn't exactly match our predefined categories.
7. Only use the "I'm sorry, I can only tell about this platform" response for queries that are completely unrelated to education, learning, the platform, or the user's data.
"""

        # Use a better AI model if available
        model = "gpt-3.5-turbo"
        
        response = client.chat.completions.create(
            model=model,  
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