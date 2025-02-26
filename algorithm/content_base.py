import psycopg2
import pandas as pd

conn = psycopg2.connect(
    dbname="webappDB",
    user="postgres",
    password="11042001/05/2004",
    host="localhost",
    port="5432"
)

cur = conn.cursor()

query = """
SELECT user_id, displayname_text, rate 
FROM user_ratings;
"""

df_ratings = pd.read_sql(query, conn)

# Pivot to create user-item matrix (Users × Restaurants)
user_rating_matrix = df_ratings.pivot(index='user_id', columns='displayname_text', values='rate').fillna(0)

print(user_rating_matrix.head())  # Check matrix

conn.close()

print(user_rating_matrix)