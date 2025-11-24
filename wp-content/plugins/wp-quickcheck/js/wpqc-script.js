//1-Disable the submit button until the text input has at least 3 characters using jQuery
//2- Show a small counter of how many characters the user typed using jQuery.
//3- AJAX endpoint Add an AJAX endpoint that returns the last five saved entries as JSON.

jQuery(document).ready(function ($) {
  var submitButton = $("#qc_submit");
  var textInput = $("#qc_input");
  var inputLabel = $("label[for='qc_input']");
  var charCountDisplay = $("#char_count");
  var list = $("#entries_list");
  var minChars = 3;

  // Listen for input changes in the text field
  textInput.on("input", function () {
    let charCount = $(this).val().length;

    // Check the length of the input value
    if (charCount >= minChars) {
      // Enable the submit button if input has at least 3 characters
      submitButton.prop("disabled", false);
      textInput.addClass("qc-success").removeClass("qc-error");
    } else {
      // Disable the submit button if input has less than 3 characters
      submitButton.prop("disabled", true);
      textInput.removeClass("qc-success");
    }

    //Show a small counter of how many characters the user typed
    charCountDisplay.text(charCount + " characters");
  });

  // Handle form submission (no POST refresh)
  $("#qc").on("submit", function (e) {
    e.preventDefault();
    var input = textInput.val().trim();
    if (!input) return;
    $.ajax({
      url: wpqc_ajax.ajax_url,
      method: "POST",
      data: {
        action: "wpqc_store_input",
        input: input,
        nonce: wpqc_ajax.nonce,
      },
      success: function (response) {
        if (!response.success) {
          textInput.removeClass("qc-success");
          textInput.addClass("qc-error");
          //   inputLabel.text(response.data.message);
          //   inputLabel.css("color", "red");
          inputLabel.after(
            "<span class='qc-error-message' style='color:red;'>" +
              response.data.message +
              "</span>"
          );
          console.error("Save error:", response.data);
          return;
        }

        // Clear field after save
        textInput.val("");
        charCountDisplay.text("");
        submitButton.prop("disabled", true);
        textInput.removeClass("qc-success");
        if ($(".qc-error-message")) {
          $(".qc-error-message").remove();
        }

        // Refresh the entries list
        loadEntries();
      },
      error: function (err) {
        console.error("AJAX error:", err);
      },
    });
  });

  // Function to fetch last 5 entries
  function loadEntries() {
    $.ajax({
      url: wpqc_ajax.ajax_url,
      method: "GET",
      data: {
        action: "wpqc_get_last_five_entries",
        nonce: wpqc_ajax.nonce,
      },
      success: function (entries) {
        if (entries === "0") {
          console.error("Unauthorized");
          return;
        }

        list.empty();

        entries.forEach(function (row) {
          var listItem = $("<li>");
          var smallTag = $("<small>");
          // .text() automatically escapes any HTML characters.
          listItem.text(row.input_text + " ");
          smallTag.text("(" + row.submitted_at + ")");
          listItem.append(smallTag);
          list.append(listItem);
        });
      },
      error: function (err) {
        console.error("AJAX error:", err);
      },
    });
  }

  // Load entries on page load
  loadEntries();
});
