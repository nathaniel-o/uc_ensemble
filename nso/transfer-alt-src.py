import json

# Paths
drinks_path = 'c:/xampp/htdocs/wordpress/wp-content/nso/drinks-26Dec24.json'
media_path = 'c:/xampp/htdocs/wordpress/wp-content/nso/media-uploads-28Dec24.json'

# Load drinks data
with open(drinks_path, 'r') as f:
    drinks = json.load(f)

def normalize_name(name):
    """Normalize cocktail name for comparison by:
    - Converting to lowercase
    - Removing apostrophes
    - Replacing spaces, ampersands, and other special characters
    - Handling common abbreviations
    - Removing two-letter prefixes (e.g., FP_, AU_, etc.)
    """
    normalized = (name.strip()
            .lower()
            .replace("'", "")  # Remove apostrophes
            .replace(" g&t", " gt")  # Handle G&T -> GT before general & replacement
            .replace("&", "and")  # Handle other ampersands
            .replace(" ", "-")  # Replace spaces with hyphens
            .replace("_", "-")  # Handle underscores in filenames
            .replace("with-sugar-crust", "")  # Remove extra descriptors
            .replace("with-", "")  # Remove 'with' prefix
            )
    
    # Remove common two-letter prefixes if they exist
    prefixes = ["fp-", "au-", "wi-", "sp-", "su-", "ro-", "ev-", "so-"]
    for prefix in prefixes:
        if normalized.startswith(prefix):
            normalized = normalized[3:]  # Remove prefix and the hyphen
            break
    
    # Remove common suffixes
    suffixes = ["-t", "-scaled", "-rotated"]
    for suffix in suffixes:
        if normalized.endswith(suffix):
            normalized = normalized[:-len(suffix)]
    
    return normalized

def parse_media_file():
    """Parse the media uploads JSON and return all file URLs"""
    with open(media_path, 'r') as f:
        media_data = json.load(f)
    
    # Extract URLs from the new JSON structure
    media_urls = []
    for entry in media_data[2]['data']:  # Access the data array in the third object
        media_urls.append(entry['file_url'])
    
    print(f"Found {len(media_urls)} media URLs")
    return media_urls

def update_drinks_with_alt_src():
    """Update drinks with alt-src arrays and populate empty src fields"""
    media_urls = parse_media_file()
    modified_count = 0
    match_count = 0
    src_updates = 0
    
    for drink in drinks:
        # Initialize alt-src array for every drink
        drink['alt-src'] = []
        modified_count += 1
        
        # Skip matching for empty cocktail names
        if not drink['cocktail']:
            continue
            
        normalized_drink = normalize_name(drink['cocktail'])
        
        # Look for matching media entries
        for url in media_urls:
            # Extract the filename without extension and path
            filename = url.split('/')[-1].rsplit('.', 1)[0]  # Handle both .jpg and .jpeg
            normalized_url = normalize_name(filename)
            
            if normalized_drink in normalized_url:    # in > ==
                full_url = f"wp-content/uploads/{url}"  # Add WordPress uploads prefix
                drink['alt-src'].append(full_url)
                match_count += 1
                
                # If src is empty, use this URL
                if not drink['src']:
                    drink['src'] = full_url
                    src_updates += 1
                    print(f"Updated empty src for '{drink['cocktail']}' with: {full_url}")
        
    print(f"\nPopulated {src_updates} empty src fields")
    return modified_count, match_count

# Run the update
modified_count, match_count = update_drinks_with_alt_src()
print(f"\nAdded alt-src array to {modified_count} drinks")
print(f"Found {match_count} matching images")

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
        has_alt_src = sum(1 for drink in verification if 'alt-src' in drink)
        has_matches = sum(1 for drink in verification if drink.get('alt-src', []))
        print(f"\nVerification: Found {has_alt_src} drinks with alt-src array")
        print(f"Verification: Found {has_matches} drinks with matching images")
except Exception as e:
    print(f"Error verifying file: {e}")




def run_tests():
    """Run test cases for name normalization"""
    test_cases = [
        # (cocktail name, filename, should match)
    #    ("Dracula's Necromancer", "SP_Draculas-Necromancer-2", True),
    #    ("Eggnog Martini", "Eggnog-martini-T-2", True),
    #    ("Nutty Sweet Manhattan", "Nutty-sweet-manhattan-with-sugar-crust-T", True),
    #    ("Ginger Rose Martini", "FP_Ginger-Rose-Martini-T-scaled", True),
    #    ("Quiet Celebration Martini", "SO_Quiet-Celebration-Martini-T-scaled", True),
        # Add negative test cases
    #    ("Manhattan", "Martini", False),
    ]

    passed = 0
    failed = 0

    print("\nRunning normalization tests:")
    print("---------------------------")
    
    for cocktail, filename, should_match in test_cases:
        norm_cocktail = normalize_name(cocktail)
        norm_filename = normalize_name(filename)
        matches = norm_cocktail in norm_filename
        
        if matches == should_match:
            passed += 1
            result = "✓ PASS"
        else:
            failed += 1
            result = "✗ FAIL"
            
        print(f"{result} | {cocktail} -> {norm_cocktail}")
        print(f"      {filename} -> {norm_filename}")
        
    print(f"\nResults: {passed} passed, {failed} failed")
    return failed == 0

# Run tests if script is run directly
if __name__ == "__main__":
    success = run_tests()
    if not success:
        print("\nSome tests failed!")
    else:
        print("\nAll tests passed!")