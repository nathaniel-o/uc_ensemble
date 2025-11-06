import json
import re

# Read the JSON file
with open('c:/xampp/htdocs/wordpress/wp-content/nso/metadata-26Dec24.json', 'r', encoding='utf-8') as file:
    data = json.load(file)

print("Starting processing...")

# Filter and combine the entries
filtered_data = {}
for filename, content in data.items():
    # Check if entry has xmp object with non-empty Xmp.dc.subject array
    if ('xmp' in content and 
        'Xmp.dc.subject' in content['xmp'] and 
        isinstance(content['xmp']['Xmp.dc.subject'], list) and 
        len(content['xmp']['Xmp.dc.subject']) > 0):
        
        # Extract base filename by removing both -T and -number variations
        base_filename = re.sub(r'(-T|-\d+)?(?=\.[^.]+$)', '', filename)
        
        # Debug print
        if base_filename != filename:
            print(f"Found duplicate: {filename} -> {base_filename}")
        
        # Keep only the first occurrence of each base filename
        if base_filename not in filtered_data:
            filtered_data[base_filename] = content
            print(f"Added: {base_filename}")
        else:
            print(f"Skipped duplicate: {filename}")

# Write the filtered data back to the file
output_path = 'c:/xampp/htdocs/wordpress/wp-content/nso/metadata-26Dec24.json'
with open(output_path, 'w', encoding='utf-8') as file:
        json.dump(filtered_data, file, indent=4)

# Print some statistics
removed_count = len(data) - len(filtered_data)
print(f"\nSummary:")
print(f"Original entries: {len(data)}")
print(f"Filtered entries: {len(filtered_data)}")
print(f"Removed entries: {removed_count}")
print(f"Output written to: {output_path}")



