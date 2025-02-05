import json

# Load drinks data
drinks_path = 'c:/xampp/htdocs/wordpress/wp-content/nso/drinks-26Dec24.json'

with open(drinks_path, 'r') as f:
    drinks = json.load(f)

# Find drinks with missing src
missing_src = []
for drink in drinks:
    if drink['cocktail'] and not drink['src']:  # Only include if cocktail name exists
        missing_src.append({
            'cocktail': drink['cocktail'],
            'pageCode': drink['pageCode']
        })

# Print results
if missing_src:
    print("\nCocktails missing src images:")
    print("-----------------------------")
    for drink in missing_src:
        print(f"• {drink['cocktail']} ({drink['pageCode']})")
    print(f"\nTotal: {len(missing_src)} cocktails missing images")
else:
    print("No cocktails missing src images found!")