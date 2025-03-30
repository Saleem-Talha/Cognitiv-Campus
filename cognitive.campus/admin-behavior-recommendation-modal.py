import mysql.connector
import pandas as pd
import re
import string
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from dotenv import load_dotenv
import os

load_dotenv()

def get_db_connection():
    """Establish database connection using environment variables"""
    return mysql.connector.connect(
        host=os.getenv('DB_HOST'),
        user=os.getenv('DB_USER'), 
        password=os.getenv('DB_PASS'),
        database=os.getenv('DB')
    )

def clean_text(text):
    """
    Comprehensive text cleaning function
    - Remove HTML tags
    - Remove specific template phrases
    - Convert to lowercase
    - Remove punctuation
    - Remove extra whitespaces
    """
    # Handle potential None or empty input
    if not text:
        return ""
    
    # Convert to string to handle various input types
    text = str(text)
    
    # Remove HTML tags
    text = re.sub(r'<[^>]+>', '', text)
    
    # Remove specific template phrases
    text = re.sub(r'Note taking\.\.\. starter template', '', text, flags=re.IGNORECASE)
    text = re.sub(r'--Ai generated notes--', '', text, flags=re.IGNORECASE)
    
    # Convert to lowercase
    text = text.lower()
    
    # Remove punctuation
    text = text.translate(str.maketrans('', '', string.punctuation))
    
    # Remove extra whitespaces
    text = ' '.join(text.split())
    
    return text

def fetch_notes():
    """
    Fetch notes from notes_course and notes_projects tables
    Returns a dictionary with note ID and cleaned content
    Includes both content and page_title in the cleaned text
    """
    try:
        connection = get_db_connection()
        cursor = connection.cursor()
        
        notes = {}
        
        # Fetch from notes_course
        cursor.execute("SELECT id, page_title, content FROM notes_course")
        for note_id, page_title, content in cursor.fetchall():
            # Combine page_title and content for comprehensive text
            combined_text = f"{clean_text(page_title)} {clean_text(content)}"
            if combined_text.strip():
                notes[f'course_note_{note_id}'] = combined_text
        
        # Fetch from notes_projects
        cursor.execute("SELECT id, page_title, content FROM notes_project")
        for note_id, page_title, content in cursor.fetchall():
            # Combine page_title and content for comprehensive text
            combined_text = f"{clean_text(page_title)} {clean_text(content)}"
            if combined_text.strip():
                notes[f'project_note_{note_id}'] = combined_text
        
        print(f"Debug: Total notes found: {len(notes)}")
        for key, content in notes.items():
            print(f"Debug: {key} - Content length: {len(content)}")
        
        return notes
    
    except mysql.connector.Error as err:
        print(f"Database Error: {err}")
        return {}
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()

def get_notes_recommendations(csv_file, notes):
    """Generate course recommendations based on notes"""
    try:
        # Read CSV file
        with open(csv_file, 'r', encoding='utf-8', errors='replace') as file:
            df = pd.read_csv(file)
        
        # Prepare the text for comparison
        df['combined'] = df['Course Name'] + " " + df['Course Description'] + " " + df['Skills']
        df['combined'] = df['combined'].apply(clean_text)
        
        # Create corpus with input notes and dataset courses
        notes_corpus = list(notes.values())
        course_corpus = df['combined'].tolist()
        
        # Combine for vectorization
        full_corpus = notes_corpus + course_corpus
        
        # Vectorize
        vectorizer = TfidfVectorizer(stop_words='english')
        tfidf_matrix = vectorizer.fit_transform(full_corpus)
        
        # Calculate similarities
        notes_similarities = cosine_similarity(
            tfidf_matrix[:len(notes_corpus)], 
            tfidf_matrix[len(notes_corpus):]
        )
        
        all_recommendations = {}
        
        # Process each note
        for idx, (note_key, note_content) in enumerate(notes.items()):
            print(f"\nGenerating recommendations for: {note_key}")
            
            # Get top 5 similar courses
            note_similarities = notes_similarities[idx]
            top_5_indices = note_similarities.argsort()[-5:][::-1]
            
            recommendations = []
            for i, index in enumerate(top_5_indices, 1):
                try:
                    course_info = {
                        'Note_ID': note_key,
                        'Course Name': df.iloc[index]['Course Name'],
                        'University': df.iloc[index]['University'],
                        'Difficulty': df.iloc[index]['Difficulty Level'],
                        'Rating': df.iloc[index]['Course Rating'],
                        'URL': df.iloc[index]['Course URL'],
                        'Skills': df.iloc[index]['Skills'],
                        'Similarity': float(note_similarities[index])
                    }
                    recommendations.append(course_info)
                    
                    # Print recommendation details
                    print(f"\nRecommendation #{i}:")
                    print(f"Course: {course_info['Course Name']}")
                    print(f"Similarity Score: {course_info['Similarity']:.4f}")
                except Exception as e:
                    print(f"Skipping problematic row due to error: {e}")
                    continue
            
            all_recommendations[note_key] = recommendations
        
        return all_recommendations
    
    except Exception as e:
        print(f"Error in recommendation process: {e}")
        return {}

def save_recommendations(recommendations):
    """Save recommendations to notes_recommendations table"""
    try:
        connection = get_db_connection()
        cursor = connection.cursor()
        
        # Clear existing recommendations
        cursor.execute("TRUNCATE TABLE notes_recommendations")
        
        # Prepare insert statement
        insert_query = """
        INSERT INTO notes_recommendations 
        (note_id, source_note_type, recommended_course_name, recommendation_rank, 
        similarity_score, university, difficulty_level, 
        course_rating, course_url, course_skills) 
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        # Process each note's recommendations
        for note_key, recs in recommendations.items():
            for rank, rec in enumerate(recs, 1):
                values = (
                    int(note_key.split('_')[-1]),  # Extract ID from note_key
                    note_key.split('_')[0],  # note type (course_note or project_note)
                    rec['Course Name'],
                    rank,
                    rec['Similarity'],
                    rec['University'],
                    rec['Difficulty'],
                    rec['Rating'],
                    rec['URL'],
                    rec['Skills']
                )
                cursor.execute(insert_query, values)
        
        connection.commit()
        print("Successfully saved recommendations to database!")
        
    except mysql.connector.Error as err:
        print(f"Error saving recommendations: {err}")
        connection.rollback()
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()

def main():
    # Fetch notes from database
    notes = fetch_notes()
    
    if not notes:
        print("No notes found in the database!")
        return
    
    print(f"Found {len(notes)} notes:")
    for key in notes.keys():
        print(f"- {key}")
    
    # Get recommendations
    try:
        csv_path = r'E:\xampp\htdocs\cognitive.campus\dataset\Coursera.csv'
        recommendations = get_notes_recommendations(csv_path, notes)
        
        if recommendations:
            # Save recommendations to database
            save_recommendations(recommendations)
        else:
            print("No recommendations were generated.")
    except Exception as e:
        print(f"Error in recommendation process: {e}")
        return

if __name__ == "__main__":
    main()