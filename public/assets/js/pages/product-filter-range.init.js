/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************************************!*\
  !*** ./resources/js/pages/product-filter-range.init.js ***!
  \*********************************************************/
/*
Template Name: FOMS ACADEMY Business Management System
Author: Web Mahal Web Service
Website: https://webmahal.com/
Contact: webmahal@gmail.com
File: Property list filter init js
*/
var slider = document.getElementById('priceslider');
noUiSlider.create(slider, {
  start: [250, 800],
  connect: true,
  tooltips: true,
  range: {
    'min': 0,
    'max': 1000
  },
  pips: {
    mode: 'count',
    values: 5
  }
});
/******/ })()
;
