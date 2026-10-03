import os
import json
import pyexiv2
import logging
from datetime import datetime

def setup_logging(log_file):
    logging.basicConfig(
        level=logging.ERROR,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=[
            logging.FileHandler(log_file),
            logging.StreamHandler()
        ]
    )

def extract_metadata(image_path):
    metadata = {}
    try:
        with pyexiv2.Image(image_path) as img:
            xmp_data = img.read_xmp()
            
            # Skip if XMP data is empty
            if xmp_data:
                # Remove unwanted keys
                keys_to_remove = [
                    'Xmp.xmpMM.InstanceID',
                    'Xmp.MicrosoftPhoto.LastKeywordXMP'
                ]
                for key in keys_to_remove:
                    xmp_data.pop(key, None)
                
                # Only add to metadata if there's remaining data
                if xmp_data:
                    metadata['xmp'] = xmp_data

    except Exception as e:
        logging.error(f"Error processing {image_path}: {str(e)}")
        metadata['error'] = str(e)

    return metadata

def process_images(directory):
    metadata_dict = {}
    # Walk through directory and all subdirectories
    for root, dirs, files in os.walk(directory):
        for filename in files:
            if filename.lower().endswith(('.png', '.jpg', '.jpeg', '.tiff', '.bmp', '.gif')):
                try:
                    image_path = os.path.join(root, filename)
                    # Store relative path instead of just filename
                    relative_path = os.path.relpath(image_path, directory)
                    metadata = extract_metadata(image_path)
                    # Only add to dictionary if there's metadata and it's not empty
                    if metadata and 'xmp' in metadata and metadata['xmp']:
                        metadata_dict[relative_path] = metadata
                except Exception as e:
                    logging.error(f"Error processing file {filename}: {str(e)}")
    return metadata_dict

# ... rest of the code remains the same ...


def save_metadata_to_json(metadata_dict, output_file):
    try:
        with open(output_file, 'w', encoding='utf-8') as json_file:
            json.dump(metadata_dict, json_file, indent=4, ensure_ascii=False)
    except Exception as e:
        logging.error(f"Error saving JSON file: {str(e)}")

if __name__ == "__main__":
    # Create timestamp for log file
    timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
    log_file = f'metadata_extraction_{timestamp}.log'
    
    # Setup logging
    setup_logging(log_file)
    
    try:
        #Use an backup of the Uploads folder as caution
        directory = 'c:/xampp/htdocs/wordpress/wp-content/uploads/'
        output_file = 'metadata-26Dec24.json'
        
        logging.info(f"Starting metadata extraction from {directory}")
        metadata_dict = process_images(directory)
        save_metadata_to_json(metadata_dict, output_file)
        logging.info("Metadata extraction completed")
        
    except Exception as e:
        logging.error(f"Fatal error: {str(e)}")