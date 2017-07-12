from app import app

import json
import os

def get_parameters():
    filename = os.path.join(app.static_folder, 'config.json')
    with open(filename) as config_file:
        json_object = config_file
        json_readable = json.load(json_object) 
    
    return json_readable
