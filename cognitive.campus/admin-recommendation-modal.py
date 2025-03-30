import mysql.connector
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from datetime import datetime
from dotenv import load_dotenv
import os

load_dotenv()

# Initialize database connection
def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv('DB_HOST'),
        user=os.getenv('DB_USER'),
        password=os.getenv('DB_PASS'),
        database=os.getenv('DB')
    )

def fetch_course_names_and_ids():
    """Fetch course names and IDs from both tables and return as a dictionary"""
    try:
        connection = get_db_connection()
        cursor = connection.cursor()
        
        # Get courses from both tables
        cursor.execute("SELECT id, course_name FROM course_status")
        status_courses = {row[1]: row[0] for row in cursor.fetchall()}
        
        cursor.execute("SELECT id, name FROM own_course")
        own_courses = {row[1]: row[0] for row in cursor.fetchall()}
        
        # Combine while maintaining uniqueness
        all_courses = {**status_courses, **own_courses}
        
        return all_courses
        
    except mysql.connector.Error as err:
        print(f"Database Error: {err}")
        return {}
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

def save_recommendations(recommendations, course_ids):
    """Save recommendations to the database"""
    try:
        connection = get_db_connection()
        cursor = connection.cursor()
        
        # Clear existing recommendations before inserting new ones
        cursor.execute("TRUNCATE TABLE course_recommendations")
        
        # Prepare insert statement with all fields
        insert_query = """
        INSERT INTO course_recommendations 
        (source_course_id, source_course_name, recommended_course_name, recommendation_rank, similarity_score, 
         university, difficulty_level, course_rating, course_url, course_description, skills) 
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        # Process each course and its recommendations
        for source_course, recs in recommendations.items():
            source_id = course_ids.get(source_course)
            
            for rank, rec in enumerate(recs, 1):
                values = (
                    source_id,
                    source_course,
                    rec['Course Name'],
                    rank,
                    rec['Similarity'],
                    rec['University'],
                    rec['Difficulty'],
                    rec['Rating'],
                    rec['URL'],
                    rec.get('Course Description', ''),
                    rec['Skills']
                )
                cursor.execute(insert_query, values)
        
        connection.commit()
        print("Successfully saved recommendations to database!")
        
    except mysql.connector.Error as err:
        print(f"Error saving recommendations: {err}")
        connection.rollback()
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()


def get_course_recommendations(csv_file, course_names):
    """Get top 5 recommendations for each course"""
    try:
        # Open the CSV file and read with error handling
        with open(csv_file, 'r', encoding='utf-8', errors='replace') as file:
            df = pd.read_csv(file)
        
        # Prepare the text for comparison
        df['combined'] = df['Course Name'] + " " + df['Course Description'] + " " + df['Skills']
        
        # Create corpus with input courses
        course_corpus = list(course_names.keys()) + df['combined'].tolist()
        
        # Vectorize
        vectorizer = TfidfVectorizer(stop_words='english')
        tfidf_matrix = vectorizer.fit_transform(course_corpus)
        
        # Calculate similarities
        similarities = cosine_similarity(tfidf_matrix[:len(course_names)], tfidf_matrix[len(course_names):])
        
        all_recommendations = {}
        
        # Process each course
        for idx, course in enumerate(course_names.keys()):
            print(f"\nGenerating recommendations for: {course}")
            
            # Get top 5 similar courses
            course_similarities = similarities[idx]
            top_5_indices = course_similarities.argsort()[-5:][::-1]
            
            recommendations = []
            for i, index in enumerate(top_5_indices, 1):
                try:
                    # Skip rows with invalid characters or empty fields
                    course_info = {
                        'Course Name': df.iloc[index]['Course Name'],
                        'University': df.iloc[index]['University'],
                        'Difficulty': df.iloc[index]['Difficulty Level'],
                        'Rating': df.iloc[index]['Course Rating'],
                        'URL': df.iloc[index]['Course URL'],
                        'Skills': df.iloc[index]['Skills'],
                        'Similarity': float(course_similarities[index])
                    }
                    recommendations.append(course_info)
                    
                    # Print formatted recommendation
                    print(f"\nRecommendation #{i}:")
                    print(f"Course: {course_info['Course Name']}")
                    print(f"Similarity Score: {course_info['Similarity']:.4f}")
                except Exception as e:
                    print(f"Skipping problematic row due to error: {e}")
                    continue
            
            all_recommendations[course] = recommendations
        
        return all_recommendations
    
    except Exception as e:
        print(f"Error in recommendation process: {e}")
        return {}

def main():
    # Get all courses and their IDs from database
    course_dict = fetch_course_names_and_ids()
    
    if not course_dict:
        print("No courses found in the database!")
        return
    
    print(f"Found {len(course_dict)} courses in database:")
    for course in course_dict.keys():
        print(f"- {course}")
    
    # Get recommendations
    try:
        csv_path = r'E:\xampp\htdocs\cognitive.campus\dataset\Coursera.csv'
        recommendations = get_course_recommendations(csv_path, course_dict)
        if recommendations:
            # Save recommendations to database
            save_recommendations(recommendations, course_dict)
        else:
            print("No recommendations were generated.")
    except Exception as e:
        print(f"Error in recommendation process: {e}")
        return

if __name__ == "__main__":
    main()
