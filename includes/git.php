<?php

  function roots_current_git_head(){
    $head = @file(ABSPATH . '../../../.git/HEAD' );

    if(!$head) return '';

    return $head[0];
  }

  function root_current_git_commit($branch = null) {
    if(!$branch) $branch = root_current_git_branch();

     if($branch === false){
       $hash = roots_current_git_head();
     }else{
       $hash = @file(ABSPATH . '../../../.git/refs/heads/' . $branch);
       if($hash) $hash = $hash[0];
     }

     if ( $hash ) {
      return substr($hash, 0, 7);
    }
  }

  function root_current_git_branch() {
    $head = roots_current_git_head();

    $explodedstring = explode("/", $head, 3);

    if(count($explodedstring) != 3) return false;

    $branchname = $explodedstring[2];

    return trim($branchname);
  }