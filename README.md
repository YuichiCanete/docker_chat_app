docker-compose build
docker-compose up -d
./init_db.sh

goto localhost:8000

docker-compose down