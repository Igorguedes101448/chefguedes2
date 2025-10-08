"""
User Settings Module for ChefGuedes2

This module provides functionality to manage user settings for the application.
"""

import json
import os
from typing import Any, Dict, Optional


class UserSettings:
    """Manages user settings with persistence to a JSON file."""
    
    def __init__(self, settings_file: str = "settings.json"):
        """
        Initialize UserSettings.
        
        Args:
            settings_file: Path to the settings file
        """
        self.settings_file = settings_file
        self.settings: Dict[str, Any] = {}
        self.load()
    
    def load(self) -> None:
        """Load settings from file."""
        if os.path.exists(self.settings_file):
            try:
                with open(self.settings_file, 'r') as f:
                    self.settings = json.load(f)
            except (json.JSONDecodeError, IOError) as e:
                print(f"Error loading settings: {e}")
                self.settings = {}
        else:
            self.settings = {}
    
    def save(self) -> None:
        """Save settings to file."""
        try:
            with open(self.settings_file, 'w') as f:
                json.dump(self.settings, f, indent=2)
        except IOError as e:
            print(f"Error saving settings: {e}")
    
    def get(self, key: str, default: Any = None) -> Any:
        """
        Get a setting value.
        
        Args:
            key: Setting key
            default: Default value if key doesn't exist
            
        Returns:
            Setting value or default
        """
        return self.settings.get(key, default)
    
    def set(self, key: str, value: Any) -> None:
        """
        Set a setting value.
        
        Args:
            key: Setting key
            value: Setting value
        """
        self.settings[key] = value
        self.save()
    
    def delete(self, key: str) -> bool:
        """
        Delete a setting.
        
        Args:
            key: Setting key
            
        Returns:
            True if deleted, False if key didn't exist
        """
        if key in self.settings:
            del self.settings[key]
            self.save()
            return True
        return False
    
    def get_all(self) -> Dict[str, Any]:
        """
        Get all settings.
        
        Returns:
            Dictionary of all settings
        """
        return self.settings.copy()
    
    def clear(self) -> None:
        """Clear all settings."""
        self.settings = {}
        self.save()
