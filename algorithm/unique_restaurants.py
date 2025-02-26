import pandas as pd
import ast

df = pd.read_csv('algorithm/restaurants_drop_clean.csv')
df['types'] = df['types'].apply(ast.literal_eval)
unique_types = set(item for sublist in df['types'] for item in sublist)

# Display result
print(unique_types)
print(len(unique_types))



group_a = unique_types
group_b = {"vegan_restaurant", "vegetarian_restaurant", "bar_and_grill", 
            "barbecue_restaurant", 
            "breakfast_restaurant", 
            "brunch_restaurant", 
            "buffet_restaurant", 
            "cafeteria", 
            "fast_food_restaurant", 
            "food_delivery", 
            "hamburger_restaurant", 
            "juice_shop", 
            "meal_delivery", 
            "meal_takeaway", 
            "pizza_restaurant", 
            "steak_house", 
            "wine_bar", "asian_restaurant", 
            "brazilian_restaurant", 
            "chinese_restaurant", 
            "dessert_restaurant", 
            "french_restaurant", 
            "greek_restaurant", 
            "indian_restaurant", 
            "italian_restaurant", 
            "japanese_restaurant", 
            "lebanese_restaurant", 
            "mexican_restaurant", 
            "ramen_restaurant", 
            "seafood_restaurant", 
            "sushi_restaurant", 
            "thai_restaurant", 
            "turkish_restaurant", 
            "vietnamese_restaurant", "deli", "bakery", "market", "home_goods_store", "convenience_store",
                "liquor_store", "food_store", "gift_shop", "sandwich_shop", "food_court", "cafe", "coffee_shop", "dessert_shop", "ice_cream_shop",
                "candy_store", "butcher_shop", "store", "tea_house", "health", "confectionery","event_venue", "karaoke", "park", "sports_activity_location", "sports_club", "night_club",
                "performing_arts_theater", "wedding_venue", "museum", "dog_cafe", "catering_service", "child_care_agency", "massage"}

missing_in_group_b = group_a - group_b

# Find the missing items in Group B but not in Group A
missing_in_group_a = group_b - group_a


sorted_list = {
    'american_restaurant', 'bar', 'bed_and_breakfast', 'community_center', 'clothing_store', 'diner',
    'establishment', 'fine_dining_restaurant', 'food', 'farm', 'garden', 'guest_house', 'hotel',
    'korean_restaurant', 'lodging', 'mediterranean_restaurant', 'middle_eastern_restaurant',
    'point_of_interest', 'pub', 'restaurant', 'shopping_mall', 'spanish_restaurant', 'wholesaler'
}
missing_in_group = missing_in_group_b - sorted_list

# Output the missing items in both sets
print("Items in Group A but not in Group B:")
print(missing_in_group_b)

print("\nItems in Group B but not in Group A:")
print(missing_in_group_a)

print()
print(missing_in_group)