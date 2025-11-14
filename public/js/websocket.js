let socket;

function connectWebSocket() {
    socket = new WebSocket('wss://localhost:7011/ws'); // Replace 'wss' with 'ws' if not using HTTPS

    socket.onopen = function (event) {
        console.log('WebSocket opened');
    };

    socket.onmessage = function (event) {
        var data = event.data;
        console.log('WebSocket message received:', data);
        // Trigger a custom event to notify other scripts
        const eventDetail = { detail: { data: data } };
        document.dispatchEvent(new CustomEvent('websocketMessage', eventDetail));
    };

    socket.onclose = function (event) {
        console.log('WebSocket closed');
    };

    socket.onerror = function (error) {
        console.error('WebSocket error:', error);
    };
}

// Call the function to connect the WebSocket
connectWebSocket();