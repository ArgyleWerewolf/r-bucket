document.addEventListener("DOMContentLoaded", function (event) {
  var uploadChecks = document.querySelectorAll(".uploadCheck");
  var multiIdHolders = document.querySelectorAll(".multiIds");
  var uploadMultiActions = document.getElementById("uploadMultiActions");
  var idsToProcess = [];
  uploadChecks.forEach(function (uploadCard) {
    uploadCard.addEventListener('change', function () {
      if (this.checked) {
        idsToProcess.push(this.value);
      } else {
        var value = this.value;
        idsToProcess = idsToProcess.filter(function (item) {
          return item !== value
        })
      }
      if (idsToProcess.length > 0) {
        uploadMultiActions.classList.remove('hide');
      } else {
        uploadMultiActions.classList.add('hide');
      }
      multiIdHolders.forEach(function (holder) {
        holder.value = idsToProcess;
      });
    });
  });

});

function handleFiles(files) {
  if (files[0]) {
    var file = files[0];
    var fileNamePreview = document.getElementById("fileNamePreview");
    var fileSelectorLabelWrapper = document.getElementById("fileSelectorLabelWrapper");
    var fileUploadButton = document.getElementById("fileUploadButton");
    fileSelectorLabelWrapper.classList.toggle('hide');
    fileUploadButton.classList.toggle('hide');
    fileNamePreview.textContent = file.name;
  }
}

