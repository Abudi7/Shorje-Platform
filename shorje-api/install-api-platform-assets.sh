#!/bin/bash

# Script to install API Platform assets
echo "Installing API Platform assets..."

# Create the assets directory if it doesn't exist
mkdir -p public/assets/bundles/apiplatform

# Copy API Platform assets from vendor to public directory
cp -r vendor/api-platform/core/src/Symfony/Bundle/Resources/public/* public/assets/bundles/apiplatform/

echo "API Platform assets installed successfully!"
echo "You can now access the API documentation at: http://localhost:8000/api/docs.html"
