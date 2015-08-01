<?php

function root_obfuscate($value){
  if($value == '') return $value;
  $safe = '';

  foreach (str_split($value) as $letter)
  {
    if (ord($letter) > 128) return $letter;

    // To properly obfuscate the value, we will randomly convert each letter to
    // its entity or hexadecimal representation, keeping a bot from sniffing
    // the randomly obfuscated letters out of the string on the responses.
    switch (rand(1, 3))
    {
      case 1:
        $safe .= '&#'.ord($letter).';'; break;

      case 2:
        $safe .= '&#x'.dechex(ord($letter)).';'; break;

      case 3:
        $safe .= $letter;
    }
  }

  return $safe;
}

/**
 * Obfuscate all emails in the content and custom fields
 */
function root_obfuscate_email($email){
  return str_replace('@', '&#64;', root_obfuscate($email));
}

function root_obfuscate_email_callback($email){
  return root_obfuscate_email($email[0]);
}

function root_obfuscate_content_emails($content){
  $content = preg_replace_callback("#([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)#", 'root_obfuscate_email_callback', $content);
  return $content;
}
add_filter('the_content', 'root_obfuscate_content_emails', 100);
add_filter('acf/format_value/type=wysiwyg', 'root_obfuscate_content_emails', 100);
add_filter('acf/format_value/type=text', 'root_obfuscate_content_emails', 100);
add_filter('acf/format_value/type=email', 'root_obfuscate_content_emails', 100);
add_filter('acf/format_value/type=textarea', 'root_obfuscate_content_emails', 100);

