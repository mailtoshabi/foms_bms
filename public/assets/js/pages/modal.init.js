/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!******************************************!*\
  !*** ./resources/js/pages/modal.init.js ***!
  \******************************************/
/*
Template Name: FOMS ACADEMY Business Management System
Author: Web Mahal Web Service
Website: https://webmahal.com/
Contact: webmahal@gmail.com
File: Modal init js
*/
var exampleModal = document.getElementById('exampleModal');
exampleModal.addEventListener('show.bs.modal', function (event) {
  // Button that triggered the modal
  var button = event.relatedTarget; // Extract info from data-bs-* attributes

  var recipient = button.getAttribute('data-bs-whatever'); // If necessary, you could initiate an AJAX request here
  // and then do the updating in a callback.
  //
  // Update the modal's content.

  var modalTitle = exampleModal.querySelector('.modal-title');
  var modalBodyInput = exampleModal.querySelector('.modal-body input');
  modalTitle.textContent = 'New message to ' + recipient;
  modalBodyInput.value = recipient;
});
/******/ })()
;
