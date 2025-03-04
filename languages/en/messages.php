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
]; 