# wp-quickcheck is a simple plugin for WordPress to create a shortcode with simple form with input and submit button with AJAX functionality.

# It create a shortcode [qc_form] that outputs a form with a single text input and a submit button.

# When the form is submitted, it should use AJAX to send the input value to a custom AJAX endpoint.

# The shortcode should also load any necessary JavaScript and CSS assets for the form and AJAX functionality.

## JS and AJAX functionality

# 1- Disable the submit button until the text input has at least 3 characters using jQuery

# 2- Show a small counter of how many characters the user typed using jQuery.

# 3- Add an AJAX endpoint that returns the last five saved entries as JSON.

# 4- User can add multiple instances of the shortcode on the same page without conflicts.

### Extra functionality

# 5- On page load, if the user is logged in, fetch and display the last five entries using jQuery AJAX.

# 6- Add some UX improvements and progressive enhancement (Loader indication, success indication, error handling, etc).
