const express = require('express');
const router = express.Router();
const mysql = require('mysql2/promise'); // Use 'mysql2/promise' for async/await support

// Create a connection pool
const pool = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'drug-dispensing-system',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Middleware for secure endpoints
const authenticateToken = (req, res, next) => {
    const apiKey = req.header('Authorization');

    if (!apiKey) {
        return res.status(401).json({ message: 'Unauthorized - API Key missing' });
    }

    // Example: Check if the API key is valid
    checkApiKey(apiKey)
        .then((isValidKey) => {
            if (isValidKey) {
                next(); // Continue to the next middleware
            } else {
                res.status(403).json({ message: 'Forbidden - Invalid API Key' });
            }
        })
        .catch((err) => {
            console.error('Error checking API key:', err);
            res.status(500).json({ error: 'Internal Server Error' });
        });
};

// Function to check if the API key is valid
const checkApiKey = async (apiKey) => {
    const query = 'SELECT user_id FROM users WHERE api_key = ?';

    const [rows, fields] = await pool.execute(query, [apiKey]);

    // Check if the key exists in the database
    return rows.length > 0;
};

// Function to set up routes for the admin module
const setupAdminRoutes = (app, db, jwt) => {
    // Add/Edit a new drug category
    router.post('/drugcategory', authenticateToken, async (req, res) => {
        const category = req.body;
        try {
            const [result] = await db.execute('INSERT INTO drug_categories SET ?', category);
            res.json({ message: 'Drug category added successfully' });
        } catch (err) {
            res.status(500).json({ error: err.message });
        }
    });

    // Add/Edit a new user
    router.post('/user', authenticateToken, async (req, res) => {
        const user = req.body;
        try {
            const [result] = await db.execute('INSERT INTO users SET ?', user);
            res.json({ message: 'User added successfully' });
        } catch (err) {
            res.status(500).json({ error: err.message });
        }
    });

    // Add/Edit a new drug and assign categories
    router.post('/drug', authenticateToken, async (req, res) => {
        const drug = req.body;
        try {
            const [result] = await db.execute('INSERT INTO drugs SET ?', drug);
            res.json({ message: 'Drug added successfully' });
        } catch (err) {
            res.status(500).json({ error: err.message });
        }
    });

    // Generate tokens/user access keys for API access
    router.post('/generateToken', authenticateToken, (req, res) => {
        const user = req.body.user;
        const token = jwt.sign(user, process.env.JWT_SECRET, { expiresIn: '1h' });
        res.json({ token });
    });

    // Use the router in the app
    app.use('/admin', router);
};

// Export the function for setting up routes
module.exports = setupAdminRoutes;


