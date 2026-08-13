$(function () {
  (function () {
    const $scrollableBlock = $(".chat-inner");
    $scrollableBlock.scrollTop($scrollableBlock[0].scrollHeight);
    $scrollableBlock.css("opacity", 1);
  })();

  let scrollInterval;

  $(".scroll-up")
    .on("click", function () {
      scrollUp(this);
      scrollInterval = setInterval(scrollUp, 150);
    })
    .on("mouseup mouseleave", function () {
      clearInterval(scrollInterval);
    });

  $(".scroll-down")
    .on("click", function () {
      scrollDown(this); // Call the scrollDown function
      scrollInterval = setInterval(scrollDown, 150); // Start the interval for continuous scrolling
    })
    .on("mouseup mouseleave", function () {
      clearInterval(scrollInterval);
    });

  function scrollUp(_this) {
    $(_this)
      .closest(".scroll-box")
      .find(".scroll-block")
      .animate({ scrollTop: "-=50" }, 100);
  }
  function scrollDown(_this) {
    $(_this)
      .closest(".scroll-box")
      .find(".scroll-block")
      .animate({ scrollTop: "+=50" }, 100);
  }
  function handleFileSelect(event) {
    var $input = $(event.target);
    var $previewContainer = $input
      .closest(".dropzone-container")
      .find(".loaded-images__container");
  

  
    var files = event.target.files;
    for (var i = 0; i < files.length; i++) {
      var file = files[i];
  
      var reader = new FileReader();
  
      reader.onload = (function (file) {
        return function (event) {
          var $imageContainer = $("<div>")
            .addClass("uploaded-image-container")
            .attr("data-index", files.length);
          var $imageStyles = $("<div>").addClass("uploaded-image-styles");
          $input
            .closest(".dropzone-container")
            .find(".loadedInner")
            .addClass("images-preview-on");
  
          var $image = $("<img>")
            .attr("src", event.target.result)
            .addClass("uploaded-image");
  
          $imageStyles.append($("<span>")).on("click", function () {
            // Remove the image container when span is clicked
            var $imageContainer = $(this).closest(".uploaded-image-container");
            var index = $imageContainer.data("index"); // Get the file index from data attribute
            $imageContainer.remove();
  
            // Remove the corresponding file from the FileReader object
            files.splice(index, 1);
          });
  
          $imageStyles.append($image);
  
          $imageContainer.append($imageStyles);
  
          $previewContainer.append($imageContainer);
        };
      })(file);
  
      reader.readAsDataURL(file);
    }
  

  }
  
  // Bind file input change event
  $(".fileInput").on("change", handleFileSelect);
  
});
