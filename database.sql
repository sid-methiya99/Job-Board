-- Create database
CREATE DATABASE IF NOT EXISTS jobportal;
USE jobportal;

-- Users table (both employers and job seekers)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('employer', 'jobseeker') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Employer specific fields
    company_logo VARCHAR(255),
    company_website VARCHAR(255),
    company_size VARCHAR(50),
    company_industry VARCHAR(100),
    company_location VARCHAR(100),
    company_description TEXT,
    -- Job seeker specific fields
    skills TEXT,
    experience TEXT,
    resume VARCHAR(255),
    linkedin_url VARCHAR(255),
    github_url VARCHAR(255)
);

-- Jobs table
CREATE TABLE jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    location VARCHAR(100) NOT NULL,
    salary VARCHAR(100),
    employment_type VARCHAR(50),
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Applications table
CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    cover_letter TEXT,
    resume VARCHAR(255),
    status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE
);

-- Insert sample companies
INSERT INTO users (name, email, password, user_type, company_logo, company_website, company_size, company_industry, company_location, company_description) VALUES
('Google', 'hr.careers@google.com', '$2y$10$dummyhashedpassword1', 'employer', 'google-logo.png', 'https://google.com', '100,000+', 'Technology', 'Mountain View, CA', 'Google is a global technology leader, with a mission to organize the world''s information and make it universally accessible and useful.'),

('Microsoft', 'hr.careers@microsoft.com', '$2y$10$dummyhashedpassword2', 'employer', 'microsoft-logo.png', 'https://microsoft.com', '150,000+', 'Technology', 'Redmond, WA', 'Microsoft is a leading global provider of computer software, cloud services, and technology solutions.'),

('Apple', 'hr.careers@apple.com', '$2y$10$dummyhashedpassword3', 'employer', 'apple-logo.png', 'https://apple.com', '150,000+', 'Technology', 'Cupertino, CA', 'Apple revolutionizes personal technology with products that transform how people work, learn, and connect.'),

('Meta', 'hr.careers@meta.com', '$2y$10$dummyhashedpassword4', 'employer', 'meta-logo.png', 'https://meta.com', '50,000+', 'Technology', 'Menlo Park, CA', 'Meta builds technologies that help people connect, find communities, and grow businesses.'),

('Amazon', 'hr.careers@amazon.com', '$2y$10$dummyhashedpassword5', 'employer', 'amazon-logo.png', 'https://amazon.com', '1,000,000+', 'Technology & E-commerce', 'Seattle, WA', 'Amazon is guided by four principles: customer obsession, passion for invention, commitment to operational excellence, and long-term thinking.');

-- Insert sample job listings
INSERT INTO jobs (employer_id, title, description, requirements, location, salary, employment_type, category, created_at) VALUES
(1, 'Senior Software Engineer', 'Join Google''s engineering team to build next-generation solutions that define the future of technology.', '- 8+ years of software development experience
- Strong expertise in distributed systems
- Experience with large-scale applications
- Masters or PhD in Computer Science preferred', 'Mountain View, CA', '$150,000 - $250,000', 'Full-time', 'Technology', NOW()),

(1, 'Product Manager', 'Lead product strategy and execution for Google''s innovative products.', '- 5+ years of product management experience
- Strong analytical and problem-solving skills
- Experience with data-driven decision making
- MBA preferred', 'Mountain View, CA', '$140,000 - $220,000', 'Full-time', 'Technology', NOW()),

(2, 'Cloud Solutions Architect', 'Design and implement cloud solutions for Microsoft''s enterprise customers.', '- 6+ years of cloud architecture experience
- Strong knowledge of Azure services
- Experience with enterprise architecture
- Relevant certifications preferred', 'Redmond, WA', '$130,000 - $200,000', 'Full-time', 'Technology', NOW()),

(3, 'iOS Developer', 'Create amazing experiences for Apple''s mobile platforms.', '- 5+ years of iOS development experience
- Strong knowledge of Swift and Objective-C
- Experience with Apple''s Human Interface Guidelines
- Background in mobile UI/UX', 'Cupertino, CA', '$140,000 - $210,000', 'Full-time', 'Technology', NOW()),

(4, 'AR/VR Engineer', 'Build the future of virtual and augmented reality at Meta.', '- 4+ years of AR/VR development experience
- Strong 3D mathematics and computer graphics knowledge
- Experience with Unity or Unreal Engine
- Background in computer vision', 'Menlo Park, CA', '$160,000 - $240,000', 'Full-time', 'Technology', NOW()),

(5, 'Machine Learning Engineer', 'Develop AI solutions for Amazon''s retail and cloud platforms.', '- 5+ years of machine learning experience
- Strong programming skills in Python
- Experience with deep learning frameworks
- PhD in Machine Learning or related field preferred', 'Seattle, WA', '$150,000 - $230,000', 'Full-time', 'Technology', NOW()),

(5, 'Frontend Developer', 'Create engaging user experiences for Amazon''s web platforms.', '- 3+ years of frontend development experience
- Expertise in React, JavaScript, and CSS
- Experience with responsive design
- Knowledge of web accessibility standards', 'Seattle, WA', '$120,000 - $180,000', 'Full-time', 'Technology', NOW());