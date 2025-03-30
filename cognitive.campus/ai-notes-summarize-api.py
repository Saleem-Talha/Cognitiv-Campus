import sys
import os
import json
import re
from dotenv import load_dotenv
from openai import OpenAI

# Load environment variables
load_dotenv()

# Initialize OpenAI client
client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))

def clean_content(content):
    # Remove common template phrases and clean up the text
    content = re.sub(r'Note Taking\.\.\.', '', content, flags=re.IGNORECASE)
    content = re.sub(r'Starter Template', '', content, flags=re.IGNORECASE)
    
    # Remove extra whitespace
    content = ' '.join(content.split())
    
    return content

def is_meaningful_content(content):
    # Check if content is meaningful
    # Criteria: 
    # 1. More than 50 characters
    # 2. Contains multiple words
    # 3. Not just random characters
    if len(content) < 50:
        return False
    
    # Check word count
    words = content.split()
    if len(words) < 5:
        return False
    
    # Check for text that looks like actual content
    # This is a simple heuristic and can be improved
    meaningful_patterns = [
    r'\b(the|a|an|and|or|but|if|is|are|was|were|has|have|had|will|shall|can|could|would|should|may|might|must)\b',
    r'\b(of|in|to|for|with|on|at|by|from|as|about|into|through|over|after|before|between|under|within|without|along|against|among)\b',
    r'\b(not|no|yes|all|any|some|each|every|either|neither|both|one|two|three|many|few|several|most|much|more|less|none|own)\b'
]
    
    pattern_matches = sum(1 for pattern in meaningful_patterns if re.search(pattern, content, re.IGNORECASE))
    
    return pattern_matches > 0

def summarize_content(content):
    try:
        # Clean and validate content
        cleaned_content = clean_content(content)
        
        # Check if content is meaningful
        if not is_meaningful_content(cleaned_content):
            return {
                'summary': 'No meaningful content found to summarize.',
                'status': 'no_content'
            }
        
        # Generate summary using GPT-3.5
        summary_response = client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=[
                {
                    "role": "system", 
                    "content": "You are a precise, professional summarization assistant. If the content seems nonsensical or lacks meaningful information, clearly state that no useful summary can be provided."
                },
                {
                    "role": "user", 
                    "content": f"Please provide a comprehensive summary of the following content. If the content appears to be gibberish or lacks coherent meaning, clearly state that no meaningful summary can be generated:\n\n{cleaned_content} But make sure the summary is complete and detailed, make sure to include important information from the content in the summary as well"
                }
            ],
            max_tokens=1000,
            temperature=0.7
        )
        
        summary = summary_response.choices[0].message.content.strip()
        
        # Additional check for meaningfulness of summary
        if len(summary) < 50 or 'no meaningful summary' in summary.lower():
            return {
                'summary': 'Unable to generate a useful summary from the provided content.',
                'status': 'no_summary'
            }
        
        return {
            'summary': summary,
            'status': 'success'
        }

    except Exception as e:
        return {
            'summary': f"Error during summarization: {str(e)}",
            'status': 'error'
        }

if __name__ == "__main__":
    # Check if content is provided as an argument
    if len(sys.argv) > 1:
        # Combine all arguments into a single string of content
        content = ' '.join(sys.argv[1:])
        response = summarize_content(content)
        
        # Output as JSON to be parsed by PHP
        print(json.dumps(response))
    else:
        print(json.dumps({'summary': 'No content provided', 'status': 'error'}))