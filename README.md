### Prerequisites
- Ensure you have [Git Bash](https://git-scm.com/) installed.
- Install [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/).
- Verify that Docker is running on your system.

### Instructions
1. Open the Git Bash terminal.
2. Build the Docker containers:
    ```bash
    docker-compose build
    ```
3. Start the containers in detached mode:
    ```bash
    docker-compose up -d
    ```
4. Initialize the database:
    ```bash
    ./init_db.sh
    ```
5. Set up and use the database:
    ```bash
    ./use_db.sh
    ```
6. Open your browser and navigate to [http://localhost:8000](http://localhost:8000) to access the application.
7. To stop the containers, run:
    ```bash
    docker-compose down
    ```