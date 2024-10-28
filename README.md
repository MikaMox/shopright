# Shop Right Code Task

The purpose of this code is to fulfill the following requirements
- Create an Inventory Management System that can track and update product stock
- Issue low stock warnings
- Issue out of stock alerts
- Save new stock quantity
- Create a NotificationService that handles low stock alerts 
- Create an Order Logger to view all processed orders
- Create a basic front end for viewing logs

## Assumptions
Although it isn't defined in the specification I have created the ProcessOrder as a REST API.  Also because I implemented
an event dispatcher and events the NotificationService does not have a sendLowStockAlert method.  Instead you dispatch
a LowStockEvent which the listener then interacts with the NotificationService to save the notification.

You will also need docker installed and working

## Configuration

I have implemented a couple of containers.  An nginx web server and php container.  The images will need to be built before
the containers can be spun up.  The set up process is as follows, from the root of the codebase issue these commands.

```
docker compose build
docker compose up
```

This should spin up the two containers.  Which you can then access via http://127.0.0.1:8080/

## Code Breakdown

### Index
The nginx configuration routes all requests that aren't static files to the index.php.

The index has the composer autoloader class included so that we can use Namespacing in our classes.  It also does the
registering of the event listeners in the dispatcher.

Lastly it instances the FrontController class and gets the response from the FrontController

### FrontController
The FrontController implements an MVC design pattern.  It registers the controllers and methods that are allowed. 
For this test there are 2 exposed controller methods:

- InventoryManager->processOrder. This returns a JsonResponse
- InventoryManager->viewLog. This returns a HtmlResponse
- InventoryManager->index. This is the default request.  I could have used it for something but it currently just resets the session

The FrontController also handles the error responses from the controllers

#### Process Order
The process order endpoint can be called using Postman or another REST client.  The request should be configured as follows:
```
POST http://127.0.0.1:8080/InventoryManager/ProcessOrder

{
    "id": 2,
    "quantity": 4
}
```

The id is the product Id and the required quantity to be processed.  Note: I have left in the capability to specify negative 
numbers for the quantity to simulate stock refills.  This should really be a separate request and negatives should be rejected


#### View Log
The log can be accessed by opening the below page in the browser

```
http://localhost:8080/InventoryManager/ViewLog
```

The first part is the Order Log.  Showing all orders processed.  It would be better if this was in reverse order with the
latest at the top.  

The second table below the first is the notifications log.  I have used the same logger for the errors also.  Although
it wasn't specified to do it this way, I feel that finding all the alerts and errors in one place is useful

#### Index (bonus)
```
http://localhost:8080/InventoryManager
```

This is just a debug action that I have left in.  It resets the session for the client.  

### Services
I have created 3 services and the all follow the same pattern.  I should have probably created an abstract class and
extended these, but I didn't have time to refactor.  The 3 classes are Products, OrderLogger & NotificationService.

They all implement the singleton pattern that you create the instance using the getInstance method.  This creates a new 
instance if the class hasn't been initiated and returns the static instance if it has.  This assures that there is only
1 instance of this class ever created. 

The services use the session to store the data.  Though I have put in a check to see if the data files have been updated
since the last load.  This is because when you are working across clients the session is not shared.  So even though 
The REST api maintains the session and log file and keeps them in sync.  If you have requested the view log page the 
session wouldn't get updated.

### Responses
I created a quick example of a response abstract and interface to show how the responses should be structured.  This
allows us to change the response headers appropriate to the required output we need.

The HtmlResponse implements basic templating capabilities.  It accepts an array of html which it will the inject into a
view.  The views are all kept within `/src/Views`

### Events
Although it wasn't specified it feels to me that low stock warnings would be an event that can be initiated, and you could
potentially have multiple listeners that would consume these events to perform housekeeping tasks.

### Data
There are 3 data files all within `/data` 

- products.json.  This one is initialised with data by the user.  I could have created endpoints to add products and manage them, but it wasn't a requirement. 
- orders.json.  This will be initialised if it doesn't exist.  So you can delete this one and it will be recreated.  It holds all the orders
- notifications.json.   This one contains the notifications.  Although the data was requested to be stored in the session (which it does).  To get this session data in the browser you need to load it from the file, otherwise we would have had to share client sessions, which isn't really appropriate
