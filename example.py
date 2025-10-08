#!/usr/bin/env python3
"""
Example usage of the UserSettings module.
"""

from user_settings import UserSettings


def main():
    # Initialize user settings
    settings = UserSettings()
    
    # Set some example settings
    print("Setting user preferences...")
    settings.set("username", "chefguedes")
    settings.set("theme", "dark")
    settings.set("language", "en")
    settings.set("notifications", True)
    
    # Get settings
    print(f"\nUsername: {settings.get('username')}")
    print(f"Theme: {settings.get('theme')}")
    print(f"Language: {settings.get('language')}")
    print(f"Notifications: {settings.get('notifications')}")
    
    # Get all settings
    print(f"\nAll settings: {settings.get_all()}")
    
    # Get non-existent setting with default
    print(f"\nFont size (default): {settings.get('font_size', 14)}")
    
    # Delete a setting
    print(f"\nDeleting 'notifications' setting...")
    settings.delete("notifications")
    
    print(f"All settings after deletion: {settings.get_all()}")


if __name__ == "__main__":
    main()
