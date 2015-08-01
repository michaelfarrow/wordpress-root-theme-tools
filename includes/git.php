<?php

  function root_current_git_commit($branch = null) {
    if(!$branch) $branch = root_current_git_branch();

     if ( $hash = @file(ABSPATH . '../../../.git/refs/heads/' . $branch)) {

      return substr($hash[0], 0, 7);
    }
  }

  function root_current_git_branch() {
    $stringfromfile = @file(ABSPATH . '../../../.git/HEAD' );

    if(!$stringfromfile) return '';

    $firstLine = $stringfromfile[0];
    $explodedstring = explode("/", $firstLine, 3);
    $branchname = $explodedstring[2];

    return trim($branchname);
  }