import sys
import json
from openai import OpenAI
import os
from dotenv import load_dotenv
import requests
import hashlib
from datetime import datetime

load_dotenv()

def generate_image_from_prompt(prompt):
    """
    Generate an image using DALL-E 3 based on the text prompt
    Saves the image locally and returns the file path
    """
    try:
        client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))
        
        # Create img/ai directory if it doesn't exist
        save_dir = 'img/ai'
        os.makedirs(save_dir, exist_ok=True)
        
        # Generate a unique filename based on prompt and timestamp
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        prompt_hash = hashlib.md5(prompt.encode()).hexdigest()[:8]
        filename = f"{timestamp}_{prompt_hash}.png"
        filepath = os.path.join(save_dir, filename)
        
        # Check if image already exists
        if os.path.exists(filepath):
            return {
                "success": True,
                "image": f"{save_dir}/{filename}",
                "cached": True
            }
        
        # Generate new image using DALL-E 3
        response = client.images.generate(
            model="dall-e-3",  # Updated to DALL-E 3
            prompt=prompt,
            size="1024x1024",  # DALL-E 3 default size
            quality="standard", # Can be "standard" or "hd"
            n=1,
            response_format="url"
        )
        
        # Download and save the image
        image_url = response.data[0].url
        image_response = requests.get(image_url)
        
        if image_response.status_code == 200:
            with open(filepath, 'wb') as f:
                f.write(image_response.content)
            
            return {
                "success": True,
                "image": f"{save_dir}/{filename}",
                "cached": False
            }
        else:
            raise Exception("Failed to download generated image")
            
    except Exception as e:
        return {
            "success": False,
            "error": str(e)
        }

def main():
    if len(sys.argv) < 2:
        result = {"success": False, "error": "No prompt provided"}
    else:
        prompt = " ".join(sys.argv[1:])
        result = generate_image_from_prompt(prompt)
    
    print(json.dumps(result))

if __name__ == "__main__":
    main()