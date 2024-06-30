#!/bin/sh

set -e

echo "Configure git with user email ${INPUT_USER_EMAIL} and user name ${INPUT_USER_NAME}"
git config --global user.email "${INPUT_USER_EMAIL}"
git config --global user.name "${INPUT_USER_NAME}"

echo "Clone the source repository repository https://github.com/${INPUT_SOURCE_REPOSITORY}"
git clone https://${INPUT_ACCESS_TOKEN}@github.com/${INPUT_SOURCE_REPOSITORY} to_split
cd to_split

echo "Add the remote repository"
git remote add split https://${INPUT_ACCESS_TOKEN}@github.com/${INPUT_TARGET_REPOSITORY}.git

# Extract the tag or branch name from GITHUB_REF
REF_NAME="${INPUT_GIT_REF##*/}"

echo "INPUT_GIT_REF: ${INPUT_GIT_REF}"
echo "REF_NAME: ${REF_NAME}"

# Check if we are on a tag or branch
if git show-ref --tags "$REF_NAME" >/dev/null 2>&1; then
  echo "We are on a tag"

  git checkout -b temp_branch ${REF_NAME}
  git subtree split --prefix=${INPUT_SOURCE_DIRECTORY} -b temp_split_branch
  git push split temp_split_branch:refs/tags/${REF_NAME}
else
  echo "We are on a branch"

  git checkout ${REF_NAME}
  git subtree split --prefix=${INPUT_SOURCE_DIRECTORY} -b temp_split_branch
  git push split temp_split_branch:refs/heads/${REF_NAME}
fi