<?php
// Add custom Theme Functions here
add_filter('language_attributes', 'custom_lang_attr');
function custom_lang_attr() {
  return 'lang="bn-BD"';
}