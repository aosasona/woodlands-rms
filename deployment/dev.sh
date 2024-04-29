# Clean up symlinks to core so that docker does not complain if this is the initial scaffolding
if [ -L "core" ]; then
  echo "[INFO] Core is a symlink. Removing..."
  rm core
fi

# If core module is already present as a proper "core" directory, then we skip the setup
if [ -d "core" ]; then
  echo "[INFO] Core module found locally. Skipping..."
else 
  # If core module is present in the parent directory, then we copy it over
  if [ -d "../core" ]; then
    echo "[INFO] Core module found in the parent directory. Copying..."
    cp -r ../core core
  else
    echo "[ERROR] You need to have the core module in the parent directory"
    exit 1
  fi
fi

echo "[INFO] Core module setup complete"
echo "[INFO] Starting the application..."
# docker compose up -d --force-recreate --build web
docker compose up -d

# For development, we can symlink the core module so that changes are reflected immediately
# NOTE: we are skipping symlink here because docker is being a little bitch about it, using volume mounts instead
if [ ! -L "core" ]; then
  echo "[INFO] Cleaning up core module for development..."
  rm -rf ./core
  ln -s ../core core
fi
