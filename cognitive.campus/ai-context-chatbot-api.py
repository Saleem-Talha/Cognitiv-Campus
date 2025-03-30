import sys
import json
from ai_context_chatbot import get_response  

if __name__ == "__main__":
    if len(sys.argv) > 2:
        user_message = sys.argv[1]
        user_email = sys.argv[2]
        start_new_chat = sys.argv[3].lower() == 'true' if len(sys.argv) > 3 else False
        response = get_response(user_message, user_email, start_new_chat)
        print(response.strip())