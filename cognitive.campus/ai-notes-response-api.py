import sys
import os
from dotenv import load_dotenv
from openai import OpenAI

# Load environment variables
load_dotenv()

# Initialize OpenAI client
client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))

def generate_response(user_input):
    try:
        # Generate text response using GPT-3.5
        response = client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=[
                {
                    "role": "system", 
                    "content": "You are a helpful assistant. Always provide complete, well-thought-out responses."
                },
                {
                    "role": "user", 
                    "content": user_input
                }
            ],
            # Increased max_tokens to ensure complete responses
            max_tokens=1000,
            # Reduced temperature for more focused responses
            temperature=0.5,
            # Added presence_penalty to encourage more complete responses
            presence_penalty=0.1,
            # Added frequency_penalty to reduce repetition
            frequency_penalty=0.1,
            # Added stream=False to ensure we get complete responses
            stream=False
        )
        
        text_content = response.choices[0].message.content.strip()
        
        return {
            'text': text_content
        }

    except Exception as e:
        return {
            'text': f"Error: {str(e)}"
        }

if __name__ == "__main__":
    # Check if an argument is provided
    if len(sys.argv) > 1:
        user_input = sys.argv[1]
        response = generate_response(user_input)
        # Output as JSON to be parsed by PHP
        import json
        print(json.dumps(response))