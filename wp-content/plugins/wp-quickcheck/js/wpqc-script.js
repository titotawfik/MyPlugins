/*
1- Disable the submit button until the text input has at least 3 characters using jQuery
2- Show a small counter of how many characters the user typed using jQuery.
3- Add an AJAX endpoint that returns the last five saved entries as JSON.
4- User can add multiple instances of the shortcode on the same page without conflicts.
## Extra functionality if time allows:
5- On page load, if the user is logged in, fetch and display the last five entries using jQuery AJAX.
6- Add some UX improvements and progressive enhancement (Loader indication, success indication, error handling, etc).
*/

jQuery(document).ready(function ($) {
  const $forms = $(".qc-form");

  $forms.each(function (index) {
    const $currentForm = $(this);
    const instance = index + 1;
    // Support multiple instances by using data attributes
    $currentForm.data("instance", instance);
    const minChars = 3;
    const $submitButton = $currentForm.find(".qc_submit-btn");
    const $textInput = $currentForm.find(".qc_text-input");
    const $inputLabel = $currentForm.find(".qc_input-label");
    const $charCount = $currentForm.find(".qc_text-output");
    const $list = $currentForm.siblings(".qc-list");

    // Function to fetch last 5 entries (defined per instance)
    function loadEntries() {
      $.ajax({
        url: wpqc_ajax.ajax_url,
        method: "GET",
        data: {
          action: "wpqc_get_last_five_entries",
          nonce: wpqc_ajax.nonce,
        },
        success: function (entries) {
          // Handle Data: Accept either a raw array or an object { success: true, data: [...] }
          let data;
          if (entries && entries.success && Array.isArray(entries.data)) {
            data = entries.data;
          } else if (Array.isArray(entries)) {
            data = entries;
          } else {
            console.warn("Invalid entries format received:", entries);
            return;
          }

          if (!$list.length) return;
          $list.empty();

          data.forEach(function (row) {
            const $listItem = $("<li>");
            const $smallTag = $("<small>");
            // .text() automatically escapes any HTML characters.
            $listItem.text(row.input_text + " ");
            $smallTag.text("(" + row.submitted_at + ")");
            $listItem.append($smallTag);
            $list.append($listItem);
          });
        },
        error: function (err) {
          console.warn("Cannot load entries, probably unauthorized.", err);
        },
      });
    }

    // Load entries on page load for logged users only
    if (typeof wpqc_ajax !== "undefined" && wpqc_ajax.is_logged_in) {
      //Add loader indication
      $list.html("<li>Loading entries...</li>");
      loadEntries();
    }

    // Listen for input changes in the text field
    $textInput.on("input", function () {
      const charCount = $(this).val().length;
      if (charCount >= minChars) {
        // Enable the submit button if input has at least minChars characters
        $submitButton.prop("disabled", false).attr("aria-disabled", "false");
        $textInput.addClass("qc-success").removeClass("qc-error");
      } else {
        // Disable the submit button if input has less than minChars
        $submitButton.prop("disabled", true).attr("aria-disabled", "true");
        $textInput.removeClass("qc-success");
      }
      // Show a small counter of how many characters the user typed
      $charCount.text(charCount + " characters");
    });

    // Handle form submission via AJAX only (no POST refresh)
    $currentForm.on("submit", function (e) {
      e.preventDefault();

      const input = $textInput.val().trim();
      if (input.length < minChars) {
        // Check again to avoid submission if input is too short
        console.log("Input too short");
        return;
      }

      $.ajax({
        url: wpqc_ajax.ajax_url,
        method: "POST",
        data: {
          action: "wpqc_store_input",
          input: input,
          nonce: wpqc_ajax.nonce,
        },
        success: function (response) {
          if (!response || !response.success) {
            $textInput.removeClass("qc-success");
            $textInput.addClass("qc-error");
            // Remove previous message and create a new one
            $currentForm.find(".qc_error-message").remove();
            const message =
              response && response.data && response.data.message
                ? response.data.message
                : "Error saving";
            $("<span>")
              .addClass("qc_error-message")
              .text(message)
              .insertAfter($inputLabel);
            console.error(
              "Save error:",
              response && response.data ? response.data : response
            );
            return;
          }
          //Add UX improvements here: clear the field, reset the counter, disable the button again
          // Show a success indication briefly
          // set timeout to remove success class after 1.2 seconds
          $("<span>")
            .addClass("qc_success-message")
            .text("Entry saved! Thank you")
            .insertAfter($inputLabel);
          setTimeout(function () {
            $currentForm.find(".qc_success-message").remove();
          }, 1200);
          // Clear field after save
          $textInput.val("");
          $charCount.text("");
          $submitButton.prop("disabled", true).attr("aria-disabled", "true");
          $textInput.removeClass("qc-success");
          $currentForm.find(".qc_error-message").remove();

          // Refresh the entries list if the user is logged in
          if (typeof wpqc_ajax !== "undefined" && wpqc_ajax.is_logged_in) {
            loadEntries();
          }
        },
        error: function (err) {
          console.error("AJAX error:", err);
        },
      });
    });
  }); // Close the $forms.each() loop
});
