<?php
include "../WEB-API-CRUD/config/db.php";

header("Content-Type: application/json");

//HTTP Verb(GET, POST, PUT, DELETE)
$requestMethod = $_SERVER["REQUEST_METHOD"];

//GET the task ID
$task_id = isset($_GET['id']) ? intval($_GET['id']) : null;

    switch($requestMethod){
        case 'POST':
            createTask();
            break;
        case 'GET':
            if($task_id){
                getTask($task_id);
            }
            else{
                getTasks();
            }
            
            break;
        case 'DELETE':
            if($task_id){
                deleteTask($task_id);
            }
            else{
                deleteAll();
            }
            break;
        
            case 'PUT':
                if($task_id){
                    updateTask($task_id);
                }
                else{
                    echo json_encode(["message" => "Task ID is required."]);
                    http_response_code(400);
                }
                break;

    default:
    http_response_code(405);
    echo json_encode(["message" => "Method not exisiting."]);
    }

mysqli_close($conn);
?>

<?php
function createTask(){
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);

    $title = $data['title'];
    $description = $data['description'];

    if (!empty($title)){
        $sql = "INSERT INTO task (title, description) VALUES ('$title', '$description')";

        if(mysqli_query($conn, $sql)){
            http_response_code(201);
            echo json_encode(["message" => "Task created!"]);
        }
        else{
            http_response_code(500);
            echo json_encode(["message" => "Error!!!"]);
        }
    }

    else{
        http_response_code(400);
        echo json_encode(["message" => "Title is required."]);
    }
}

function getTasks(){
    global $conn;

    $sql = "SELECT * FROM task";
    $result = mysqli_query($conn, $sql);

    $task = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($task);
}

function getTask($id){
    global $conn;

    $sql = "SELECT * FROM  task WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if($row = mysqli_fetch_assoc($result)){
        echo json_encode($row);
    }
    else{
        echo json_encode(["message" => "Task not found."]);
    }
}

function deleteTask($id){
    global $conn;

    $sql = "DELETE FROM task WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if($result){
        if(mysqli_affected_rows($conn) > 0){
            echo json_encode(["message" => "Task deleted successfully."]);
        }
        else{
            echo json_encode(["message" => "Task not found."]);
        }
    }
    else{
        echo json_encode(["message" => "Error deleting task: " . mysqli_error($conn)]);
    }
}

function deleteAll(){
    global $conn;

    $sql = "DELETE FROM task";
    $result = mysqli_query($conn, $sql);

    if($result){
        if(mysqli_affected_rows($conn) > 0){
            echo json_encode(["message" => "All task has been deleted."]);
        }
        else{
            echo json_encode(["message" => "Task not found."]);
        }
    }
    else{
        echo json_encode(["message" => "Error deleting task: " . mysqli_error($conn)]);
    }
}

function updateTask($id){
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);

    //Array variable to store missing fields in the PUT request
    $missingFields = [];

    //Checks each fields that are required in the PUT request. If the user didn't include it, it will store in the $missingFields
    if (empty($data['title'])) {
        $missingFields[] = 'title';
    }
    elseif (empty($data['description'])) {
        $missingFields[] = 'description';
    }
    elseif (empty($data['status'])) {
        $missingFields[] = 'status';
    }

    //If the $missingFields is not empty, it will tell the user in Postman that they have missing field/s that they need to include in their PUT request
    if (!empty($missingFields)) {
        $missingFieldsList = implode(', ', $missingFields); //The implode function separates one whole string into arrays or key-value pairs
        echo json_encode(["message" => "Missing required fields: $missingFieldsList"]); //Tells the user the missing field/s that need to be included in the PUT request
        http_response_code(400); // Bad request
        return;
    }

    //Data that are being updated in the record
    $title = $data['title'];
    $description = $data['description'];
    $status = $data['status'];

    //SQL query to update the entire task
    $sql = "UPDATE task SET title = '$title', description = '$description', status = '$status' WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if($result){
        if(mysqli_affected_rows($conn) > 0){
            echo json_encode(["message" => "Task updated successfully."]);
        }
        else{
            echo json_encode(["message" => "Task not found."]);
        }
    }
    else{
        echo json_encode(["message" => "Error updating task: " . mysqli_error($conn)]);
    }

}
?>