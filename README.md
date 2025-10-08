# ChefGuedes2

A user settings management system for applications.

## Features

- Simple and intuitive user settings API
- Persistent storage using JSON
- Get, set, and delete settings
- Default value support
- Thread-safe operations

## Installation

No external dependencies required. Just Python 3.6+.

## Usage

```python
from user_settings import UserSettings

# Initialize settings
settings = UserSettings()

# Set values
settings.set("username", "chefguedes")
settings.set("theme", "dark")

# Get values
username = settings.get("username")
theme = settings.get("theme", "light")  # with default

# Get all settings
all_settings = settings.get_all()

# Delete a setting
settings.delete("theme")

# Clear all settings
settings.clear()
```

## Example

Run the example script:

```bash
python example.py
```

## API Reference

### `UserSettings(settings_file="settings.json")`

Initialize the user settings manager.

- `load()`: Load settings from file
- `save()`: Save settings to file
- `get(key, default=None)`: Get a setting value
- `set(key, value)`: Set a setting value
- `delete(key)`: Delete a setting
- `get_all()`: Get all settings as a dictionary
- `clear()`: Clear all settings

## License

MIT
