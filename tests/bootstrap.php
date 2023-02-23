<?php

use VCR\VCR;

require __DIR__ . '/../vendor/autoload.php';

VCR::configure()
  ->setCassettePath('tests/fixtures/vcr_cassettes')
  ->enableLibraryHooks(['curl', 'stream_wrapper'])
  ->setStorage('json');
