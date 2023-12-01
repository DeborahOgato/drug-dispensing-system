// API functionalities
// apiRoutes.js

const express = require('express');
const router = express.Router();

// Function to set up routes for the API module
const setupApiRoutes = (app, db, jwt) => {
    router.get('/users', authenticateToken, (req, res) => {
        db.query('SELECT * FROM users', (err, result) => {
            if (err) {
                res.status(500).json({ error: err.message });
            } else {
                res.json(result);
            }
        });
    });

    // Add other API routes as needed

    // Use the router in the app
    app.use('/api', router);
};

// Export the function for setting up routes
module.exports = setupApiRoutes;
