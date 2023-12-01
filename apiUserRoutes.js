// apiUserRoutes.js

const express = require('express');
const router = express.Router();

// Middleware for secure endpoints
const authenticateToken = (req, res, next) => {
    const token = req.headers['authorization'];

    if (!token) {
        return res.status(401).json({ error: 'Unauthorized: Missing token' });
    }

    // Verify the token (this is a simple example; use a library like jsonwebtoken in production)
    if (token !== 'your_secret_token') {
        return res.status(401).json({ error: 'Unauthorized: Invalid token' });
    }

    next();
};

// Function to set up routes for the API user module
const setupApiUserRoutes = (app, db, jwt) => {
    // Register API user
    router.post('/register', (req, res) => {
        const user = req.body;
        db.query('INSERT INTO api_users SET ?', user, (err, result) => {
            if (err) {
                res.status(500).json({ error: err.message });
            } else {
                res.json({ message: 'API user registered successfully' });
            }
        });
    });

    // Generate API access token or key
    router.post('/generate-access-token', (req, res) => {
        const user = req.body.user;
        const token = jwt.sign(user, process.env.JWT_SECRET, { expiresIn: '1h' });
        res.json({ token });
    });

    // Subscribe to specific API products
    router.post('/subscribe', authenticateToken, (req, res) => {
        const { userId, drugId } = req.body;

        // Assuming you have a table named 'api_subscriptions'
        db.query('INSERT INTO user_subscriptions (user_id, drug_id) VALUES (?, ?)', [userId, drugId], (err, result) => {
            if (err) {
                res.status(500).json({ error: err.message });
            } else {
                res.json({ message: 'Subscription added successfully' });
            }
        });
    });

    // Secure Routes

   // List of all users (Secure endpoint)
    router.get('/secure/users', authenticateToken, (req, res) => {
    // Implement logic to fetch all users securely

    // Example: Fetch all users from the 'api_users' table
    db.query('SELECT * FROM users', (err, result) => {
        if (err) {
            res.status(500).json({ error: err.message });
        } else {
            const users = result;
            res.json(users);
        }
    });
});

   

    // Insecure Routes

  
    router.get('/insecure/drugs', (req, res) => {
   
    db.query('SELECT * FROM drugs', (err, result) => {
        if (err) {
            res.status(500).json({ error: err.message });
        } else {
            const drugs = result;
            res.json(drugs);
        }
    });
});

 

    // Use the router in the app
    app.use('/apiuser', router);
};

// Export the function for setting up routes
module.exports = setupApiUserRoutes;

