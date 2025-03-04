<?php
return [
    // General
    'welcome' => 'Welcome',
    'login' => 'Login',
    'logout' => 'Logout',
    'profile' => 'Profile',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'add' => 'Add',
    
    // Menu
    'dashboard' => 'Dashboard',
    'physical_servers' => 'Physical Servers',
    'virtual_servers' => 'Virtual Servers',
    'services' => 'Services',
    'projects' => 'Projects',
    'locations' => 'Locations',
    'users' => 'Users',
    
    // Server Management
    'server_name' => 'Server Name',
    'ip_address' => 'IP Address',
    'ram' => 'RAM',
    'cpu' => 'CPU',
    'disk' => 'Disk',
    'location' => 'Location',
    'project' => 'Project',
    'status' => 'Status',
    'created_at' => 'Created At',
    
    // Services
    'service_name' => 'Service Name',
    'port' => 'Port',
    'description' => 'Description',
    
    // Projects
    'project_name' => 'Project Name',
    'project_code' => 'Project Code',
    
    // Users
    'username' => 'Username',
    'full_name' => 'Full Name',
    'email' => 'Email',
    'password' => 'Password',
    'role' => 'Role',
    
    // Messages
    'success' => 'Operation completed successfully.',
    'error' => 'An error occurred.',
    'confirm_delete' => 'Are you sure you want to delete?',
    
    // Statuses
    'active' => 'Active',
    'passive' => 'Passive',
    'completed' => 'Completed',
    
    // Admin Page
    'user_management' => 'User Management',
    'users_list' => 'Users',
    'add_new_user' => 'Add New User',
    'edit_user' => 'Edit User',
    'user_id' => 'ID',
    'last_login' => 'Last Login',
    'actions' => 'Actions',
    'new_password' => 'New Password',
    'password_help' => 'Leave empty if you don\'t want to change the password.',
    'username_help' => 'Username cannot be changed.',
    'user_role_admin' => 'Admin',
    'user_role_user' => 'User',
    
    // Error and Success Messages
    'error_username_email_exists' => 'This username or email is already in use!',
    'error_email_exists' => 'This email is already used by another user!',
    'success_user_added' => 'User added successfully.',
    'success_user_updated' => 'User updated successfully.',
    'error_no_access' => 'You don\'t have permission to access this page!',
    
    // Physical Server Edit
    'edit_physical_server' => 'Edit Physical Server',
    'back_to_physical_servers' => 'Back to Physical Servers',
    'server_details' => 'Server Details',
    'select_location' => 'Select Location',
    'add_new_location' => 'Add New Location',
    'select_project' => 'Select Project (Optional)',
    'add_new_project' => 'Add New Project',
    'cpu_cores' => 'CPU Cores',
    'memory' => 'Memory',
    'cpu_placeholder' => 'Ex: Intel Xeon E5-2680 v4 2.40GHz',
    'memory_placeholder' => 'Ex: 64GB DDR4',
    'disk_placeholder' => 'Ex: 2x 500GB SSD RAID1',
    'update' => 'Update',
    'error_updating_server' => 'An error occurred while updating the server',
    
    // Physical Server Add
    'add_physical_server' => 'Add New Physical Server',
    'error_adding_server' => 'An error occurred while adding the server',
    'total_cores' => 'Total CPU Cores',
    'memory_capacity' => 'Memory Capacity',
    'disk_capacity' => 'Total Disk Capacity',
    
    // Physical Server Delete
    'error_has_virtual_servers' => 'This physical server cannot be deleted because it has {sayi} virtual servers attached. You need to delete the attached virtual servers first.',
    'success_server_deleted' => 'Physical server has been successfully deleted.',
    
    // Service Management
    'add_service' => 'Add New Service',
    'edit_service' => 'Edit Service',
    'default_port' => 'Default Port',
    'existing_services' => 'Existing Services',
    'service_updated' => 'Service updated successfully.',
    'service_added' => 'New service added successfully.',
    'service_usage' => 'Usage',
    'not_in_use' => 'Not in use',
    'server_count' => '{count} servers',
    'confirm_delete_service' => 'Are you sure you want to delete this service?',
    'new_service' => 'New Service',
    
    // Service Delete
    'error_service_id_missing' => 'Service ID not specified.',
    'error_service_in_use' => 'This service is being used by {count} virtual servers. Please remove the service from these servers first.',
    'success_service_deleted' => 'Service has been successfully deleted.',
    'error_deleting_service' => 'An error occurred while deleting the service: {error}',
    
    // Home Page
    'server_tracking_system' => 'Server Tracking System',
    'hardware' => 'Hardware',
    'virtual_servers_count' => '{count} virtual servers',
    'no_virtual_servers' => 'None',
    'virtual_servers_button' => 'Virtual Servers',
    'physical_server_resources' => 'Physical Server Resources:',
    'core_usage' => 'Core Usage',
    'memory_usage' => 'Memory Usage',
    'disk_usage' => 'Disk Usage',
    'cores' => 'Cores',
    'core_count' => '{used}/{total} Cores',
    'gb_count' => '{used}/{total} GB',
    'hardware_cpu' => 'Cores: {value}',
    'hardware_memory' => 'Memory: {value}',
    'hardware_disk' => 'Disk: {value}',
    'no_physical_servers' => 'No physical servers have been added yet.',
    
    // Login Page
    'login_page_title' => 'Login - Server Tracking System',
    'error_invalid_credentials' => 'Invalid username or password!',
    'login_form_title' => 'Server Tracking System',
    'username_placeholder' => 'Username',
    'password_placeholder' => 'Password',
    'login_button' => 'Login',
    
    // Location Management
    'location_management' => 'Location Management',
    'add_new_location_title' => 'Add New Location',
    'edit_location_title' => 'Edit Location',
    'location_name' => 'Location Name',
    'existing_locations' => 'Existing Locations',
    'server_count_info' => '{count} servers',
    'no_servers' => 'No servers',
    'error_location_exists' => 'Error: This location name is already in use!',
    'success_location_added' => 'New location added successfully.',
    'success_location_updated' => 'Location updated successfully.',
    'confirm_delete_location' => 'Are you sure you want to delete this location?',
    
    // Location Delete
    'error_location_has_servers' => 'This location cannot be deleted because it has attached servers.',
    'success_location_deleted' => 'Location has been successfully deleted.',
]; 