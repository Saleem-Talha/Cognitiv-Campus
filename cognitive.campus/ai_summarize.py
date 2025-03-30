import sys
import os
from dotenv import load_dotenv
import PyPDF2
from openai import OpenAI
import logging
import mysql.connector

# Load environment variables
load_dotenv()

# Configure logging
logging.basicConfig(
    level=logging.INFO, 
    format='%(asctime)s - %(levelname)s: %(message)s',
    filename='pdf_summarizer.log'
)

# Initialize OpenAI client using environment variable
client = OpenAI(
    api_key=os.getenv('OPENAI_API_KEY')
)

def get_db_connection():
    """
    Establish a connection to MySQL database using environment variables.
    
    :return: MySQL database connection
    """
    try:
        connection = mysql.connector.connect(
            host=os.getenv('DB_HOST', 'localhost'),
            user=os.getenv('DB_USER'),
            password=os.getenv('DB_PASSWORD'),
            database=os.getenv('DB')
        )
        return connection
    except mysql.connector.Error as e:
        logging.error(f"Database connection error: {e}")
        return None

def save_summary_to_db(user_email, filename, summary, status='success'):
    """
    Save PDF summary to the database.
    
    :param user_email: Email of the user
    :param filename: Name of the PDF file
    :param summary: Generated summary
    :param status: Processing status
    :return: Boolean indicating success or failure
    """
    connection = None
    try:
        connection = get_db_connection()
        if not connection:
            logging.error("Failed to establish database connection")
            return False
        
        cursor = connection.cursor()
        query = """
        INSERT INTO pdf_summaries 
        (user_email, filename, summary, processed_status) 
        VALUES (%s, %s, %s, %s)
        """
        cursor.execute(query, (user_email, filename, summary, status))
        connection.commit()
        
        logging.info(f"Summary saved to database for {user_email}")
        return True
    
    except mysql.connector.Error as e:
        logging.error(f"Database insertion error: {e}")
        return False
    
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()

def configure_logging():
    """Set up logging configuration."""
    try:
        os.makedirs('logs', exist_ok=True)
        logging.getLogger().handlers.clear()
        file_handler = logging.FileHandler('logs/pdf_summarizer.log')
        file_handler.setFormatter(logging.Formatter('%(asctime)s - %(levelname)s: %(message)s'))
        logging.getLogger().addHandler(file_handler)
    except Exception as e:
        print(f"Logging setup error: {e}")

def extract_text_from_pdf(pdf_path, max_pages=20, max_chars=50000):
    """
    Extract text from PDF with safeguards against large files.
    
    :param pdf_path: Path to the PDF file
    :param max_pages: Maximum number of pages to process
    :param max_chars: Maximum number of characters to extract
    :return: Extracted text
    """
    try:
        with open(pdf_path, 'rb') as file:
            reader = PyPDF2.PdfReader(file)
            
            # Limit pages processed
            total_pages = min(len(reader.pages), max_pages)
            
            text = ''
            for page_num in range(total_pages):
                page = reader.pages[page_num]
                page_text = page.extract_text()
                text += page_text
                
                # Stop if we've exceeded max characters
                if len(text) > max_chars:
                    text = text[:max_chars]
                    logging.warning(f"PDF truncated to {max_chars} characters")
                    break
            
            return text.strip()
    
    except Exception as e:
        logging.error(f"PDF extraction error: {e}")
        return ""

def generate_comprehensive_prompt(text):
    """
    Create a comprehensive prompt that asks for multiple aspects of the document.
    
    :param text: Extracted text from PDF
    :return: Detailed prompt for AI
    """
    prompt = f"""Analyze the following document and provide a comprehensive summary that includes:

1. Document Type/Genre: What kind of document is this? (e.g., academic paper, report, manual, etc.)
2. Main Topic: Briefly describe the central theme or subject
3. Key Points: List the most important points or arguments
4. Purpose: What seems to be the document's primary purpose or goal?
5. Target Audience: Who was this document likely written for?
The response must be well structured , each point should begin from a new line

Text to analyze:
{text}

Please be concise but informative, using no more than 300 tokens."""
    
    return prompt

def summarize_text(text):
    """
    Summarize text using OpenAI with comprehensive instructions.
    
    :param text: Text to summarize
    :return: Comprehensive summary
    """
    try:
        # Use the comprehensive prompt
        prompt = generate_comprehensive_prompt(text)
        
        # Make API call with careful token management
        response = client.chat.completions.create(
            model="gpt-3.5-turbo-16k",  # Use 16k model for longer contexts
            messages=[
                {"role": "system", "content": "You are an expert document analyzer and summarizer."},
                {"role": "user", "content": prompt}
            ],
            max_tokens=300,  # Limit response tokens
            temperature=0.7,  # Balanced creativity and accuracy
        )
        
        summary = response.choices[0].message.content.strip()
        logging.info("Successfully generated summary")
        return summary
    
    except Exception as e:
        logging.error(f"Summarization error: {e}")
        return f"Unable to generate summary. Technical details: {str(e)}"

def validate_pdf(pdf_path):
    """
    Validate PDF file before processing.
    
    :param pdf_path: Path to PDF file
    :return: Boolean indicating file validity
    """
    try:
        # Check file exists
        if not os.path.exists(pdf_path):
            logging.error(f"File not found: {pdf_path}")
            return False
        
        # Check file size (limit to 50MB)
        max_size = 50 * 1024 * 1024  # 50MB
        if os.path.getsize(pdf_path) > max_size:
            logging.warning(f"PDF exceeds maximum size of 50MB: {pdf_path}")
            return False
        
        # Attempt to open PDF
        with open(pdf_path, 'rb') as file:
            try:
                PyPDF2.PdfReader(file)
            except Exception as e:
                logging.error(f"Invalid PDF file: {e}")
                return False
        
        return True
    
    except Exception as e:
        logging.error(f"PDF validation error: {e}")
        return False

def main():
    """Main execution function with comprehensive error handling."""
    configure_logging()
    
    # Check for correct arguments
    if len(sys.argv) < 3:
        logging.error("Insufficient arguments")
        print("Error: Please provide PDF file path and user email")
        sys.exit(1)
    
    pdf_path = sys.argv[1]
    user_email = sys.argv[2]
    
    try:
        # Validate PDF
        if not validate_pdf(pdf_path):
            print("Error: Invalid or unsupported PDF file")
            sys.exit(1)
        
        # Extract text
        pdf_text = extract_text_from_pdf(pdf_path)
        
        if not pdf_text:
            print("Error: Could not extract text from PDF")
            sys.exit(1)
        
        # Generate summary
        summary = summarize_text(pdf_text)
        
        # Save summary to database
        save_summary_to_db(user_email, os.path.basename(pdf_path), summary)
        
        # Print summary to stdout
        print(summary)
        
        # Optional: Remove uploaded file
        os.remove(pdf_path)
        logging.info(f"Processed and removed temporary file: {pdf_path}")
    
    except Exception as e:
        logging.critical(f"Unexpected error in main execution: {e}")
        # Attempt to save error status to database
        save_summary_to_db(user_email, os.path.basename(pdf_path), str(e), status='error')
        print(f"Critical error processing PDF: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()