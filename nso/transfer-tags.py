import json
import os

# Paths
metadata_path = 'c:/xampp/htdocs/wordpress/wp-content/nso/metadata-26Dec24.json'  #  produced by GetTagsFromWindows.py 
drinks_path = 'c:/xampp/htdocs/wordpress/wp-content/nso/drinks-26Dec24.json'  #  the output 

# Load the files
with open(metadata_path, 'r') as f:
    metadata = json.load(f)

with open(drinks_path, 'r') as f:
    drinks = json.load(f)

def normalize_cocktail_name(name):
    """Normalize cocktail name for comparison"""
    return name.strip().lower().replace(" ", "-")

def get_matching_metadata_entry(cocktail_name):
    """Find the matching metadata entry"""
    normalized_name = normalize_cocktail_name(cocktail_name)
    print(f"Looking for matches for: {normalized_name}")
    
    # Debug: Print first few potential matches
    potential_matches = [k for k in metadata.keys() if normalized_name in k.lower()]
    if potential_matches:
        print(f"Potential matches found: {potential_matches[:3]}")
    
    # Look for any matching filename that contains the normalized cocktail name
    matches = [k for k in metadata.keys() 
              if normalize_cocktail_name(k.replace('\\', '/').split('/')[-1].split('_')[0]) == normalized_name]
    
    if matches:
        print(f"Found matching entry: {matches[0]}")
        return metadata[matches[0]]
    
    print(f"No match found for: {normalized_name}")
    return None

# Process each drink
modified_count = 0
for drink in drinks:
    cocktail_name = drink['cocktail'].strip()
    print(f"\nProcessing cocktail: {cocktail_name}")
    
    # Find matching metadata
    metadata_entry = get_matching_metadata_entry(cocktail_name)
    
    if metadata_entry and 'xmp' in metadata_entry and 'Xmp.dc.subject' in metadata_entry['xmp']:
        # Add tags from metadata
        drink['tags'] = metadata_entry['xmp']['Xmp.dc.subject']
        print(f"Added {len(drink['tags'])} tags")
        modified_count += 1
    else:
        # Add empty tags array if no match found
        drink['tags'] = []
        print("No tags added")

print(f"\nModified {modified_count} drinks with tags")

# Save updated drinks.json
try:
    with open(drinks_path, 'w') as f:
        json.dump(drinks, f, indent=4)
    print(f"Successfully wrote updated data to {drinks_path}")
except Exception as e:
    print(f"Error writing to file: {e}")

# Verify the file was written
try:
    with open(drinks_path, 'r') as f:
        verification = json.load(f)
        has_tags = sum(1 for drink in verification if 'tags' in drink)
        print(f"\nVerification: Found {has_tags} drinks with tags in saved file")
except Exception as e:
    print(f"Error verifying file: {e}")