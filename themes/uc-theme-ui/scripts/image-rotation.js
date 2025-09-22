
(function($) {
    // Add rotation controls to media modal
    wp.media.events.on("editor:image-edit", function(editor) {
        var image = editor.image;
        var metaData = wp.media.string.props(image.attributes);
        var attachment = wp.media.attachment(metaData.id);
        var rotationValue = attachment.get("uc_rotation") || 0;
        
        // Create rotation controls if they don't exist
        if (!editor.$el.find(".uc-rotation-controls").length) {
            var $controls = $("<div class='uc-rotation-controls'>" +
                "<label>Rotate Image: </label>" +
                "<button type='button' class='button' data-rotation='90'>90°</button>" +
                "<button type='button' class='button' data-rotation='180'>180°</button>" +
                "<button type='button' class='button' data-rotation='270'>270°</button>" +
                "<button type='button' class='button' data-rotation='0'>Reset</button>" +
                "<input type='range' min='0' max='359' value='" + rotationValue + "' class='uc-custom-rotation'>" +
                "<span class='rotation-value'>" + rotationValue + "°</span>" +
                "</div>");
            
            editor.$el.find(".setting.align").after($controls);
            
            // Update rotation value display when slider changes
            $controls.find(".uc-custom-rotation").on("input", function() {
                $controls.find(".rotation-value").text($(this).val() + "°");
            });
            
            // Handle rotation button clicks
            $controls.find("button").on("click", function() {
                var rotation = $(this).data("rotation");
                applyRotation(rotation);
            });
            
            // Handle custom rotation slider
            $controls.find(".uc-custom-rotation").on("change", function() {
                var customRotation = $(this).val();
                applyRotation(customRotation, true);
            });
        }
        
        function applyRotation(rotation, isCustom) {
            // Save rotation to attachment
            attachment.save({ uc_rotation: rotation });
            
            // Update classes on image
            image.removeClass("rotate-90 rotate-180 rotate-270 rotate-custom");
            
            if (isCustom) {
                image.addClass("rotate-custom");
                image.css("--rotation-angle", rotation + "deg");
            } else if (rotation > 0) {
                image.addClass("rotate-" + rotation);
            }
            
            // Update hidden input field
            var $hiddenField = image.closest(".wp-block-image").find("input[name='uc_rotation']");
            if (!$hiddenField.length) {
                $hiddenField = $("<input>").attr({
                    type: "hidden",
                    name: "uc_rotation",
                    value: rotation
                });
                image.closest(".wp-block-image").append($hiddenField);
            } else {
                $hiddenField.val(rotation);
            }
        }
        
        // Apply stored rotation when editing
        if (rotationValue) {
            if ([90, 180, 270].includes(parseInt(rotationValue, 10))) {
                image.addClass("rotate-" + rotationValue);
            } else {
                image.addClass("rotate-custom");
                image.css("--rotation-angle", rotationValue + "deg");
            }
        }
    });
})(jQuery);