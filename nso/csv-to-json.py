import csv
import json

# Read CSV file
def csv_to_json(csv_file_path, json_file_path):
    # Create a list to store the data
    data = []
    
    # Read the CSV file
    with open(csv_file_path, 'r', encoding='utf-8') as csv_file:
        # Create CSV reader
        csv_reader = csv.DictReader(csv_file)
        
        # Convert each row into dict and append to data
        for row in csv_reader:
            data.append(row)
    
    # Write to JSON file
    with open(json_file_path, 'w', encoding='utf-8') as json_file:
        json.dump(data, json_file, indent=4)

    print(f"Converted {csv_file_path} to {json_file_path}")

# Example usage
csv_file = r'C:\Users\nathaniel\Downloads\Tier Drop-down Categories by Cocktail - Sheet1.csv'
json_file = r'C:\xampp\htdocs\wordpress\wp-content\nso\drinks-26Dec24.json'
csv_to_json(csv_file, json_file)