#!/bin/bash

# Define the SQL dump file path
SQL_FILE="chat_app.sql"

# Check if the SQL file exists
if [ ! -f "$SQL_FILE" ]; then
  echo "Error: SQL file '$SQL_FILE' not found!"
  exit 1
fi

# Execute the SQL dump inside the MySQL container
docker exec -i chat_app_db mysql -u root -proot chat_app <<EOF
$(cat "$SQL_FILE")
EOF

echo "Database initialized successfully!"