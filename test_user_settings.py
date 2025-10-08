#!/usr/bin/env python3
"""
Unit tests for UserSettings module.
"""

import os
import json
import unittest
from user_settings import UserSettings


class TestUserSettings(unittest.TestCase):
    """Test cases for UserSettings class."""
    
    def setUp(self):
        """Set up test fixtures."""
        self.test_file = "test_settings.json"
        self.settings = UserSettings(self.test_file)
    
    def tearDown(self):
        """Clean up test fixtures."""
        if os.path.exists(self.test_file):
            os.remove(self.test_file)
    
    def test_set_and_get(self):
        """Test setting and getting values."""
        self.settings.set("key1", "value1")
        self.assertEqual(self.settings.get("key1"), "value1")
    
    def test_get_with_default(self):
        """Test getting non-existent key with default."""
        result = self.settings.get("nonexistent", "default_value")
        self.assertEqual(result, "default_value")
    
    def test_delete(self):
        """Test deleting settings."""
        self.settings.set("key1", "value1")
        self.assertTrue(self.settings.delete("key1"))
        self.assertIsNone(self.settings.get("key1"))
        self.assertFalse(self.settings.delete("key1"))
    
    def test_get_all(self):
        """Test getting all settings."""
        self.settings.set("key1", "value1")
        self.settings.set("key2", "value2")
        all_settings = self.settings.get_all()
        self.assertEqual(all_settings, {"key1": "value1", "key2": "value2"})
    
    def test_clear(self):
        """Test clearing all settings."""
        self.settings.set("key1", "value1")
        self.settings.set("key2", "value2")
        self.settings.clear()
        self.assertEqual(self.settings.get_all(), {})
    
    def test_persistence(self):
        """Test that settings persist across instances."""
        self.settings.set("persistent_key", "persistent_value")
        
        # Create new instance with same file
        new_settings = UserSettings(self.test_file)
        self.assertEqual(new_settings.get("persistent_key"), "persistent_value")
    
    def test_various_data_types(self):
        """Test storing various data types."""
        self.settings.set("string", "hello")
        self.settings.set("number", 42)
        self.settings.set("float", 3.14)
        self.settings.set("boolean", True)
        self.settings.set("list", [1, 2, 3])
        self.settings.set("dict", {"nested": "value"})
        
        self.assertEqual(self.settings.get("string"), "hello")
        self.assertEqual(self.settings.get("number"), 42)
        self.assertEqual(self.settings.get("float"), 3.14)
        self.assertEqual(self.settings.get("boolean"), True)
        self.assertEqual(self.settings.get("list"), [1, 2, 3])
        self.assertEqual(self.settings.get("dict"), {"nested": "value"})


if __name__ == "__main__":
    unittest.main()
