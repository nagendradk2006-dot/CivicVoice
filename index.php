<!DOCTYPE html>
<html>

<head>
    <title>CivicVoice</title>
</head>

<body>

    <div id="welcome">
        <img src="logo.png" alt="CivicVoice Logo">
        <h1>Welcome to CivicVoice</h1>
        <p>Your Voice. Better Community.</p>
    </div>


    <div id="issues">
        <h2>Issue Categories</h2>

        <ul>
            <li>Roads</li>
            <li>Water Supply</li>
            <li>Garbage</li>
            <li>Street Lights</li>
            <li>Drainage System</li>
            <li>Electricity</li>
            <li>Parks & Trees</li>
            <li>Public Transport</li>
            <li>Traffic Signals</li>
            <li>Stray Animals</li>
        </ul>
    </div>


    <div id="how-it-works">
        <h2>How CivicVoice Works</h2>

        <ol>
            <li>Register</li>
            <li>Login</li>
            <li>Report Complaint</li>
            <li>Track Complaint</li>
        </ol>
    </div>


    <div id="links">
        <h2>Useful Links</h2>

        <a href="https://www.google.com">Visit Google</a>
    </div>


    <div id="actions">
        <h2>Citizen Actions</h2>

        <button class="main-button">Register</button>

        <br><br>

        <button class="main-button">Login</button>

        <br><br>

        <button class="main-button">Report Complaint</button>

        <br><br>

        <button class="main-button">Track Status</button>
    </div>
<h2>Report a Public Issue</h2>

<form action="submit_complaint.php" method="post" enctype="multipart/form-data">
    <label>Constituency Name:</label>
<input type="text" name="constituency_name" required placeholder="Enter constituency name">
   <label>Ward Number:</label>
<input type="text" name="Ward_number" required placeholder="Enter constituency number"> 
<label>Area Name:</label>
<input type="text" name="area_name" required placeholder="Enter area name">
<label>PIN Code:</label>
<input type="text" name="pincode" required placeholder="Enter PIN code">
<label>Describe Issue:</label>
<textarea name="issue_description" required placeholder="Describe your issue here..."></textarea>
<label>Select Department:</label>

<select name="department" required>
 <option value="">Select Department</option>    
<option>Water Department</option>
    <option>Roads and Transport Department</option>
    <option>Sanitation Department</option>
    <option>Drainage Department</option>
    <option>Electrical Department</option>
    <option>Health Department</option>
    <option>Education Department</option>
    <option>Waste Management Department</option>
    <option>Public Works Department</option>
    <option>Revenue Department</option>
    <option>Municipal Department</option>
    <option>Police Department</option>
    <option>Environment Department</option>
    <option>Housing Department</option>
    <option>Other</option>
</select>
 <label>Issue Image:</label>
    <input type="file" name="issue_image" accept="image/*" required>

    <br><br>

    <button type="submit">Submit Complaint</button>
</form>
</body>

</html>