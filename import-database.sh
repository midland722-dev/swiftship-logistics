#!/bin/bash
# Railway MySQL Database Import Script
# Run this on your local machine after installing Railway CLI

set -e

echo "=== Railway Database Import ==="
echo ""

# Check if railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo "Railway CLI not found. Installing..."
    npm install -g @railway/cli
fi

# Check if user is logged in
echo "Checking Railway authentication..."
if ! railway whoami &> /dev/null; then
    echo "Please login to Railway first:"
    railway login
fi

# Link to project
echo ""
echo "Linking to Railway project..."
railway link

# Connect to MySQL
echo ""
echo "Connecting to MySQL database..."
echo "When connected, run: source database/dbs.sql"
echo ""

railway connect mysql

echo ""
echo "=== Import Complete ==="
echo "Don't forget to:"
echo "1. Set environment variables in Railway dashboard"
echo "2. Deploy your PHP admin service"
echo "3. Update .htaccess to point /admin to your Railway URL"
