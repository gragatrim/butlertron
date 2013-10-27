<?php
//Get all of the command line arguments
//b is the comma separated list of branches that you would like to merge into the destination branch
//d is the destination branch you would like the branches merged into
//r is the remote repository of the destination branch
$options = getopt("b:d:r:");
if (empty($options['r'])) {
  $remote_repository = '';
} else {
  $remote_repository = $options['r'];
}
$destination_branch = $options['d'];
$branches = explode(",", $options['b']);

foreach ($branches AS $branch) {
  echo merge_local_into_remote($branch, $destination_branch, $remote_repository);
}

/**
 * This function attempts to merge a branch into a remote repo/branch combo
 *
 * @args string $local_branch      This is the local branch you are attempting to merge
 * @args string $remote_branch     This is the remote repo you are looking to merge into
 * @args string $remote_repository This is the remote repository for the remote branch
 *
 * @return string returns an empty string on success, otherwise it returns any errors
 */
function merge_local_into_remote($local_branch, $remote_branch, $remote_repository = 'origin') {
  $error_string = '';
  $commit_check_return = check_commit_before_merge($local_branch);
  if (!empty($commit_check_return)) {
    $error_string .= 'Error in local_branch ' . $local_branch . ' some uncommited changes exist in files: ' . $commit_check_return;
  } else {
    $clean_local_branch = escapeshellarg($local_branch);
    $clean_remote_branch = escapeshellarg($remote_branch);
    $git_merge_command = 'git fetch && git checkout -q ' . $clean_local_branch . ' && git merge --no-edit ' . escapeshellarg($remote_repository) . '/' . $clean_remote_branch .
                         ' && git checkout ' . $clean_remote_branch . ' && git merge --no-edit ' . $clean_local_branch;
    exec($git_merge_command, $ouput, $error);
    if (!empty($error)) {
      $error_string .= implode("\n", $ouput);
    }
  }
  return $error_string;
}

/**
 * This function checks to make sure that there is nothing commited to the branch before we attempt to merge
 *
 * @args string $local_branch This is the branch that we are going to be checking for uncommited, but added, changes in
 *
 * @return string Empty string on success, otherwise error string on failure
 */
function check_commit_before_merge($local_branch) {
  $error_string = '';
  // This is going to check out the correct branch, then do a status, porcelain makes it an easy to parse format, then I print out any line that isn't an untracked file
  $check_command = 'git checkout -q ' . escapeshellarg($local_branch) . ' && git status --porcelain | awk \'/^[^?]{2}/ { print $2}\'';
  exec($check_command, $output, $error);
  //a bit sloppy, TODO make this check a bit more robust, and "accurate" in terms of what it should be checing and what it should return
  if ($error == '') {
    $error_string .= implode(", ", $output);
  }
  return $error_string;
}
