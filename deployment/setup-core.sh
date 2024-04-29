#!/usr/bin/env sh

# This script is used to setup the core module which is a shared dependency and directly linkd against each projects.

# Check if core is a symlink and we are not in development mode (i.e. APP_ENV=development|dev)
if [ -L "core" ]; then
  echo "Core is a symlink. Removing..."
  rm core
fi

# Check if core exists locally
if [ -d "core" ]; then
  echo "Core module found locally. Skipping..."
  exit 0
fi

# Check if it exists in the parent directory
if [ -d "../core" ]; then
  echo "Core module found in the parent directory. Copying..."
  cp -r ../core core
  exit 0
fi


# Check if the repository is private
if [ -z "$GITHUB_TOKEN" ]; then
  echo "No GITHUB_TOKEN variable provided. Exiting..."
  exit 1
fi

# If not in the local filesystem, clone from remote repository
echo "Core module not found locally. Cloning from remote repository..."
git clone https://$(GITHUB_TOKEN)@github.com/woodlands-gp/core.git core
