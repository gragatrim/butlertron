<?php
//Get all of the command line arguments
//b is the comma separated list of branches that you would like to merge into the destination branch
//d is the destination branch you would like the branches merged into
//r is the remote repository of the destination branch
$options = getopt("b:d:r:");
if (empty($options['r'])) {
  $remote_repository = '';
  $remote_repository_command = '';
} else {
  $remote_repository = $options['r'];
  $remote_repository_command = escapeshellarg($options['r'] . '/');
}
$destination_branch = escapeshellarg($options['d']);
$branches = explode(",", $options['b']);
foreach ($branches AS $branch) {
  $clean_branch = escapeshellarg($branch);
  $git_merge_command = 'git checkout ' . $clean_branch . ' && git merge --no-edit ' . $remote_repository_command . $destination_branch .
                       ' && git checkout ' . $destination_branch . ' && git merge --no-edit ' . $clean_branch;
  exec($git_merge_command, $ouput, $error);
}
