<?php

return [
    //:: General
    'app_name' => 'Construction Workforce',
    'home' => 'Home',
    'profiles' => 'Worker Profiles',
    'profile' => 'My Profile',
    'availability' => 'Availability',
    'find_workers' => 'Find Workers',
    'find_projects' => 'Find Projects',
    'projects' => 'My Projects',
    'project_management' => 'Project Management',
    'settings' => 'Settings',
    'self_employed' => 'Self-employed',
    'logout' => 'Logout',

    'common' => [
        'save' => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete',
        'close' => 'Close',
        'loading' => 'Loading...',
        'not_available' => 'N/A',
        'unknown_company' => 'Unknown Company',
        'administrator' => 'Administrator',
        'self_employed' => 'Self-employed',
        'per_hour' => '/hour',
        'no_message_provided' => 'No message provided.',
        'statuses' => [
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'ended_early' => 'Ended Early',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            'draft' => 'Draft',
            'open' => 'Open',
            'in_progress' => 'In progress',
        ],
        'validation' => [
            'company_context_required' => 'A company context is required for this action.',
        ],
    ],

    //:: Post Registration Onboarding
    'onboarding' => [
        'company' => [
            'title' => 'Company Setup',
            'description' => 'Complete your company profile to get started.',
            'name' => 'Company Name',
            'phone' => 'Phone',
            'address' => 'Address',
            'create' => 'Create Company',
            'validation' => [
                'not_eligible' => 'You are not eligible to create a company.',
            ],
        ],
    ],

    'onboarding_page' => [
        'title' => 'Onboarding',
    ],

    //:: Auth
    'auth' => [

        // Login
        'login' => [
            'title' => 'Welcome Back',
            'email' => 'Email',
            'password' => 'Password',
            'remember' => 'Remember me',
            'forgot' => 'Forgot password?',
            'submit' => 'Log in',
        ],

        // Register
        'register' => [
            'title' => 'Create Account',
            'name' => 'Full Name',
            'email' => 'Email',
            'role' => 'Select Role',
            'role_placeholder' => 'Select your role',
            'company_owner' => 'Company Owner',
            'self_employed' => 'Self-Employed',
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
            'already_registered' => 'Already registered?',
            'submit' => 'Register',
        ],

        'forgot_password' => [
            'title' => 'Forgot Password',
            'description' => 'Enter your email address and we will send you a password reset link.',
            'email' => 'Email',
            'submit' => 'Email Password Reset Link',
        ],

        'reset_password' => [
            'title' => 'Reset Password',
            'email' => 'Email',
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
            'submit' => 'Reset Password',
        ],

        'confirm_password' => [
            'title' => 'Confirm Password',
            'description' => 'This is a secure area of the application. Please confirm your password before continuing.',
            'password' => 'Password',
            'submit' => 'Confirm',
        ],

        'verify_email' => [
            'title' => 'Email Verification',
            'description' => 'Thanks for signing up! Before getting started, verify your email address using the link we sent you. If you did not receive it, we can send another.',
            'link_sent' => 'A new verification link has been sent to the email address you provided during registration.',
            'resend' => 'Resend Verification Email',
            'logout' => 'Log Out',
        ],

    ],

    //:: Welcome Page
    'welcome_page' => [
        'hero_highlight' => 'Matching',
        'subtitle' => 'Share workforce between construction companies — no downtime, no layoffs. Put idle crews to work or find skilled workers instantly when you need them.',
        'description' => 'This platform connects construction companies with available employees, understaffed companies looking for skilled workers, and self-employed contractors seeking short-term projects.',

        // Audience
        'audience_title' => 'Who is this platform for?',
        'audience_subtitle' => 'Connecting three key groups in the construction industry',

        'audience' => [
            'employers' => [
                'title' => 'Companies with Available Employees',
                'description' => "List your skilled workers when they're available between projects. Turn downtime into revenue.",
                'points' => [
                    'Maximize workforce utilization',
                    'Generate additional revenue',
                    'Easy worker management',
                ],
            ],

            'hiring' => [
                'title' => 'Companies Looking to Hire Skilled Workers',
                'description' => "Find qualified construction professionals instantly. Fill workforce gaps without the hassle.",
                'points' => [
                    'Access vetted professionals',
                    'Fast hiring process',
                    'Flexible short-term projects',
                ],
            ],

            'contractors' => [
                'title' => 'Self-Employed Contractors',
                'description' => "Discover projects instantly. Build your reputation and track your earnings all in one place.",
                'points' => [
                    'Discover projects instantly',
                    'Build your reputation',
                    'Track your earnings',
                ],
            ],
        ],

        // Badges
        'badges' => [
            'popular' => 'Popular',
        ],

        // CTA
        'cta' => [
            'title' => 'Ready to Get Started?',
            'subtitle' => 'Join thousands of construction professionals already using our platform',
            'action' => 'Create an account',
        ],

        // Actions
        'actions' => [
            'login' => 'Log in',
            'register' => 'Create an account',
        ],

        // Footer
        'footer_rights' => 'All rights reserved. A product of <a href="https://andromedasoftware.ca" target="_blank" rel="noopener noreferrer">Andromeda Software</a>.',
    ],

    //:: Home Page
    'home_page' => [
        'title' => 'Home',

        'welcome' => 'Welcome, :name!',
        'welcome_subtitle' => 'What would you like to do today?',

        'stats' => [
            'ongoing_projects' => 'Ongoing Projects',
            'pending_requests' => 'Pending Requests',
            'active_workers' => 'Active Workers',
            'total_projects' => 'Total Projects',
            'completed_projects' => 'Completed Projects',
            'total_applications' => 'Total Applications',
            'ongoing' => 'Ongoing',
            'pending' => 'Pending',
            'workers' => 'Workers',
            'projects' => 'Projects',
        ],

        'actions' => [
            'make_available' => 'Make an Employee Available',
            'make_available_desc' => "Add or update your workers' availability schedules. Quick editing in less than 30 seconds.",

            'search_worker' => 'Search for a Worker',
            'search_worker_desc' => 'Browse available workers by job, experience, and skills. Find the perfect match for your project.',

            'create_profile' => 'Create Your Profile',
            'create_profile_desc' => 'Showcase your skills and experience to get discovered by companies and land your next project.',

            'edit_profile' => 'Edit Your Profile',
            'edit_profile_desc' => 'Update your skills, experience, and availability to stay visible to employers.',

            'browse_projects' => 'Browse Projects & Opportunities',
            'browse_projects_desc' => 'Discover available projects and job opportunities. Find your next project and apply directly.',
        ],

        'quick_access' => 'Quick Access',
        'project_hub' => 'Project Hub',
        'manage_profile' => 'Manage Profile',
        'manage_profiles' => 'Manage Profiles',
        'manage_projects' => 'Manage Projects',
        'view_all_projects' => 'View All Projects',
        'manage_availability' => 'Manage Availability',
        'settings' => 'Settings',
    ],

    //:: Profiles Page
    'profiles_page' => [
        'title' => 'Worker Profiles',
        'subtitle' => 'Manage your worker profiles',
        'self_title' => 'My Profile',
        'company_title' => 'Worker Profiles',
        'self_subtitle' => 'View and manage your profile',
        'edit_profile' => 'Edit Profile',
        'empty_title' => 'No profile yet',
        'empty_desc' => 'Create your profile to start getting discovered by companies.',
        'create_profile' => 'Create Your Profile',
        'company_subtitle' => 'Manage your worker profiles',
        'add_worker' => 'Add New Worker',
        'confirm_delete' => 'Are you sure you want to delete this worker profile?',
        'empty_table' => 'No workers added yet. Click “:action” to get started.',
        'experience_years_short' => ':count yrs',

        'labels' => [
            'certifications' => 'Certifications',
            'skills' => 'Skills',
        ],

        'table' => [
            'name' => 'Name',
            'job' => 'Job / Trade',
            'experience' => 'Experience',
            'rate' => 'Rate',
            'rating' => 'Rating',
            'skills' => 'Skills',
            'actions' => 'Actions',
        ],

        'jobs' => [
            'general_labourer' => 'General Labourer',
            'electrician' => 'Electrician',
            'carpenter' => 'Carpenter',
            'plumber' => 'Plumber',
            'hvac_technician' => 'HVAC Technician',
            'heavy_equipment' => 'Heavy Equipment Operator',
            'welder' => 'Welder',
            'concrete_worker' => 'Concrete Worker',
            'roofer' => 'Roofer',
            'painter' => 'Painter',
            'mason' => 'Mason',
            'ironworker' => 'Ironworker',
            'insulator' => 'Insulator',
            'drywall_installer' => 'Drywall Installer',
        ],

        'add_modal' => [
            'title' => 'Add Worker Profile',
            'name' => 'Full Name *',
            'company' => 'Company (auto-filled)',
            'job' => 'Job / Trade Title *',
            'job_select' => 'Select a Job or Trade',
            'experience' => 'Years of Experience *',
            'rate' => 'Hourly Rate ($) *',
            'certifications' => 'Certifications (optional)',
            'certifications_placeholder' => 'Add multiple certifications by separating with commas',
            'skills' => 'Skills *',
            'skills_placeholder' => 'Add multiple skills by separating with commas',
            'saving' => 'Saving...',
            'save' => 'Create Profile',
            'cancel' => 'Cancel',
        ],

        'edit_modal' => [
            'title' => 'Edit Worker Profile - :name',
            'updating' => 'Updating...',
            'update' => 'Update Profile',
            'save' => 'Update Profile',
        ],

        'delete_modal' => [
            'title' => 'Delete Worker Profile',
            'message' => 'Permanently delete this unused worker profile? This action cannot be undone.',
            'action' => 'Delete',
            'confirm' => 'Yes, delete it',
            'cancel' => 'Cancel',
        ],

        'archive_modal' => [
            'title' => 'Archive Worker Profile',
            'message' => 'Archive this worker profile? Its project, request, and rating history will be preserved.',
            'action' => 'Archive',
            'confirm' => 'Yes, archive it',
            'cancel' => 'Cancel',
        ],

        'validation' => [
            'cannot_delete_worker' => 'Only unarchived worker profiles without business history can be permanently deleted.',
            'cannot_archive_worker' => 'Only worker profiles with resolved history and no active work or requests can be archived.',
            'cannot_edit_archived_worker' => 'Archived worker profiles cannot be edited.',
        ],

        'success' => [
            'created' => 'Worker profile created successfully.',
            'updated' => 'Worker profile updated successfully.',
            'deleted' => 'Worker profile deleted successfully.',
            'archived' => 'Worker profile archived successfully.',
        ],
    ],

    //:: Availability Page
    'availability_page' => [
        'title' => 'Availability Management',
        'subtitle' => 'Manage your workers\' availability',
        'subtitle_company' => 'Manage your workers\' availability',
        'subtitle_self' => 'Manage your availability',
        'add_availability' => 'Add Time Slot',

        'calendar' => [
            'today' => 'Today',
            'previous' => 'Previous',
            'next' => 'Next',
            'day' => 'Day',
            'week' => 'Week',
            'month' => 'Month',
            'loading' => 'Loading calendar…',
            'load_error' => 'Unable to load calendar availability.',
            'worker_filter_label' => 'Worker',
            'all_workers' => 'All workers',
            'status_legend' => 'Availability status legend',
            'no_workers' => 'There are no workers available to schedule.',
            'no_slots' => 'No availability slots in this period.',
        ],

        'status_options' => [
            'available' => 'Available',
            'booked' => 'Booked',
            'unavailable' => 'Unavailable',
        ],

        'validation' => [
            'overlap' => 'This worker already has an availability slot that overlaps this time.',
            'end_after_start' => 'End time must be later than start time.',
            'project_assignment_conflict' => 'This worker is already assigned to a project on this date.',
            'calendar_range_too_large' => 'The calendar range cannot exceed :days days.',
            'worker_not_available' => 'The selected worker is not available to you.',
            'archived_worker_cannot_receive_availability' => 'Archived workers cannot receive new or updated availability.',
        ],

        'add_modal' => [
            'title' => 'Add Availability Slot',
            'select_worker' => 'Select Worker',
            'worker' => 'Worker *',
            'date' => 'Date *',
            'start_time' => 'Start Time *',
            'end_time' => 'End Time *',
            'status' => 'Status *',
            'saving' => 'Saving...',
            'save' => 'Add Slot',
            'cancel' => 'Cancel',
        ],
        
        'edit_modal' => [
            'title' => 'Edit Availability Slot',
            'updating' => 'Updating...',
            'update' => 'Save Changes',
            'save' => 'Save Changes',
        ],

        'delete_modal' => [
            'title' => 'Delete Availability',
            'message' => 'Are you sure you want to delete this availability entry? This action cannot be undone.',
            'confirm' => 'Yes, delete it',
            'cancel' => 'Cancel',
            'item_name' => ':worker on :date',
        ],

        'success' => [
            'created' => 'Availability added successfully.',
            'updated' => 'Availability updated successfully.',
            'deleted' => 'Availability deleted successfully.',
        ],
    ],

    //:: Find Workers Page
    'find_workers_page' => [
        'title' => 'Find Workers',
        'subtitle' => 'Browse and request available workers for your projects',
        'workers_found' => 'workers found',
        'view_profile' => 'View Profile',
        'request' => 'Request Worker',
        'certifications' => 'Certifications',
        'top_skills' => 'Top Skills',
        'no_certifications' => 'No certifications added',
        'experience_years_short' => ':count yrs',
        'experience_years' => ':count years',

        'filters' => [
            'search' => 'Search by name, job, skills or certifications...',
            'job' => 'All jobs',
        ],

        'profile_modal' => [
            'title' => 'Worker Profile',
            'experience' => 'Experience',
            'rate' => 'Rate',
            'certifications' => 'Certifications',
            'skills' => 'Skills',
        ],

        'request_modal' => [
            'title' => 'Request Worker for Project',

            'rate' => 'Rate',
            'rating' => 'Rating',

            'process_title' => 'Request Process',
            'steps' => [
                'step1_self' => 'Request sent to worker',
                'step1_company' => 'Request sent to lending company',
                'self_employed' => 'Worker can accept or reject',
                'company_worker' => 'Company (Owner or Planning Manager) can accept or reject',
                'step3' => 'Phone number unlocked upon acceptance',
                'step4' => 'Project confirmed automatically',
            ],

            'select_project' => 'Select Project *',
            'company' => 'Your Company Name',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'choose_project' => 'Choose a project for this worker',
            'project_desc' => 'Optional Message (e.g. specific tasks, project details, etc.)',
            'already_requested' => 'Already Requested',
            'archived_worker_cannot_receive_request' => 'Archived workers cannot receive new requests.',
            'sending' => 'Sending Request...',
            'send' => 'Send Request',
            'cancel' => 'Cancel',
        ],

        'validation' => [
            'project_required' => 'Please select a project.',
            'project_invalid' => 'The selected project is invalid.',
            'message_max' => 'Message cannot exceed 1000 characters.',
            'worker_already_requested' => 'You already requested this worker for this project.',
        ],

        'success' => [
            'request_sent' => 'Request sent successfully.',
        ],
    ],

    //:: Find Projects Page
    'find_projects_page' => [
        'title' => 'Find Projects',
        'subtitle' => 'Browse available project opportunities and apply',
        'projects_found' => 'projects found',

        'filters' => [
            'search' => 'Search by title, company or requirements...',
            'job' => 'All jobs',
            'location' => 'All locations',
        ],

        'project_card' => [
            'duration' => 'Duration',
            'duration_day' => ':count day',
            'duration_days' => ':count days',
            'rate' => 'Rate',
            'starts' => 'Starts',
            'workers' => 'Workers',
            'capacity' => ':committed / :required',
            'staffing_progress' => 'Workers: :committed / :required',
            'remaining_capacity' => ':count remaining',
            'requirements' => 'Requirements',
            'posted_by' => 'Posted by',
            'request_join' => 'Request to Join',
            'applied' => 'Applied',
            'invited' => 'Invited',
            'active_profile_required' => 'Active worker profile required',
        ],

        'request_modal' => [
            'title' => 'Request to Join Project',
            'select_worker' => 'Select Worker *',
            'worker' => 'Select a Worker',
            'no_matching_workers' => "No workers match this project's job type.",
            'message' => 'Optional Message (e.g. specific skills, experience, questions, etc.)',
            'info' => 'Contact information will be unlocked upon acceptance',
            'already_requested' => 'Already Requested',
            'already_requested_for_project' => 'This worker already has a request for this project.',
            'sending' => 'Sending Application...',
            'send' => 'Send Application',
            'cancel' => 'Cancel',
        ],

        'validation' => [
            'archived_worker_cannot_request' => 'Archived workers cannot submit new project applications.',
            'worker_already_requested' => 'This worker was already offered to this project.',
        ],

        'success' => [
            'request_sent' => 'Request to join project sent successfully.',
        ],

        'details_modal' => [
            'title' => 'Project Details',
            'trade' => 'Trade',
            'description' => 'Description',
            'requirements' => 'Requirements',
            'operational_details' => 'Operational Details',
            'site_name' => 'Site Name',
            'address' => 'Site Address',
            'directions' => 'Directions',
            'contact' => 'Site Contact',
        ],
    ],

    //:: My Projects Page
    'projects_page' => [
        'title' => 'Projects',
        'subtitle' => "Create, manage, and track your company’s projects",
        'create_project' => 'Create Project',
        'empty_title' => 'No projects yet',
        'empty_desc' => 'Create your first project to start finding workers and filling your workforce gaps.',
        'empty_tab_title' => 'No :status projects found',
        'empty_search_description' => 'Try adjusting your search or filters.',
        'empty_tab_description' => 'You currently have no :status projects.',
        'copy_title' => ':title (Copy :count)',

        'filters' => [
            'search' => 'Search by title, location, or requirements...',
            'status' => 'All statuses',
        ],

        'tabs' => [
            'all' => 'All',
        ],

        'labels' => [
            'date_range' => ':start to :end',
            'workers_needed_singular' => ':count worker needed',
            'workers_needed_plural' => ':count workers needed',
            'workers_needed_remaining' => 'Workers Needed: :workers — :remaining Remaining',
            'staffing_progress' => 'Workers: :committed / :required',
            'remaining_capacity' => ':count remaining',
            'recruiting' => 'Recruiting',
            'staffing_closed' => 'Staffing Closed',
        ],

        'fallbacks' => [
            'no_description' => 'No description provided.',
        ],

        'actions' => [
            'edit' => 'Edit',
            'view' => 'View',
        ],

        'table' => [
            'title' => 'Title',
            'status' => 'Status',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'city' => 'City',
            'staffing' => 'Staffing',
            'actions' => 'Actions',
        ],

        'stats' => [
            'total_projects' => 'Total Projects',
            'draft' => 'Draft',
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
        ],

        'add_modal' => [
            'title' => 'Create Project',
            'project_title' => 'Project Title *',
            'description' => 'Description *',
            'start_date' => 'Start Date *',
            'end_date' => 'End Date *',
            'city' => 'City *',
            'province' => 'Province *',
            'country' => 'Country *',
            'address_line_1' => 'Address Line 1',
            'address_line_2' => 'Address Line 2',
            'postal_code' => 'Postal Code',
            'site_name' => 'Site Name',
            'directions' => 'Directions',
            'job_type' => 'Required Job Type *',
            'number_of_workers' => 'Number of Workers Needed',
            'hourly_rate' => 'Hourly Rate ($)',
            'requirements' => 'Requirements',
            'requirements_placeholder' => 'Add multiple requirements by separating with commas',
            'status' => 'Status *',
            'saving' => 'Saving...',
            'save' => 'Create Project',
            'cancel' => 'Cancel',
        ],

        'edit_modal' => [
            'title' => 'Edit Project - :title',
            'save' => 'Update Project',
        ],

        'view_modal' => [
            'title' => 'View Project - :title',
        ],

        'delete_modal' => [
            'title' => 'Delete Draft Project - :title',
            'message' => 'Delete this draft project?',
            'subtitle' => 'This action permanently removes the unused draft.',
            'confirm' => 'Yes, Delete',
        ],

        'archive_modal' => [
            'title' => 'Archive Project - :title',
            'message' => 'Archive this project?',
            'subtitle' => 'This project will be removed from your normal project list, but its staffing, request, rating, and worker history will be preserved.',
            'confirm' => 'Yes, Archive',
        ],

        'validation' => [
            'lifecycle_managed_status' => 'This project status is managed by the staffing lifecycle and cannot be edited here.',
            'open_project_must_remain_open' => 'An open project cannot be moved back to draft through ordinary editing.',
            'capacity_below_committed' => 'Worker capacity cannot be reduced below the :count committed worker(s).',
            'cannot_delete_project' => 'Only unused, unarchived draft projects can be permanently deleted.',
            'cannot_archive_project' => 'Only completed, unarchived projects can be archived.',
        ],

        'success' => [
            'created' => 'Project created successfully.',
            'updated' => 'Project updated successfully.',
            'deleted' => 'Project deleted successfully.',
            'archived' => 'Project archived successfully.',
        ],
    ],

    //:: Project Management Page
    'project_management_page' => [
        'title' => 'Project Management',
        'subtitle' => 'Your central hub for requests, project activity, and progress tracking.',
        'create_project' => 'Create Project',

        'stats' => [
            'ongoing' => 'Ongoing',
            'pending' => 'Pending',
            'completed' => 'Completed',
            'total' => 'Total',
        ],

        'tabs' => [
            'requests' => 'Requests',
            'staffing' => 'Staffing',
            'in_progress' => 'In Progress',
            'active' => 'Active',
            'completed' => 'Completed',
            'requests_sent' => 'Requests Sent',
            'requests_received' => 'Requests Received',
            'requests_join' => 'Requests to Join Projects',
            'awaiting_response_invitations' => 'Awaiting their response — Invitations',
            'awaiting_response_applications' => 'Awaiting their response — Applications',
            'needs_your_response' => 'Needs your response',
            'invitations' => 'Invitations',
            'applications' => 'Applications',
            'assignments' => 'Assignments',
            'your_active_projects' => 'Your active projects',
            'external_assignments' => 'External assignments',
            'your_project' => 'Your Project',
            'external_assignment' => 'External Assignment',
            'ongoing_projects' => 'Ongoing Projects',
            'completed_projects' => 'Completed Projects',
            'date' => 'Requested on',
            'waiting_response' => 'Waiting for response...',
            'accept' => 'Accept',
            'reject' => 'Reject',
        ],

        'sections' => [
            'completed_created' => 'Completed Projects You Created',
            'completed_joined' => "Completed Projects You've Joined",
            'completed_assignments' => 'Completed Assignments',
            'pending_activity' => 'Pending Activity',
            'ongoing_activity' => 'Ongoing Activity',
            'completed_activity' => 'Completed Activity',
        ],

        'empty_states' => [
            'no_requests' => 'No requests',
            'requests_description' => 'Requests related to your projects and workers will appear here.',
            'self_employed_requests_description' => 'Invitations and application requests will appear here.',
            'no_staffing_projects' => 'No staffing projects',
            'staffing_description' => 'Pre-start projects with accepted workers or closed recruiting will appear here.',
            'no_assignments' => 'No assignments',
            'assignments_description' => 'Accepted assignments that have not started yet will appear here.',
            'no_in_progress_projects' => 'No projects in progress',
            'in_progress_description' => 'Active project assignments will appear here.',
            'completed_description' => 'Completed project outcomes will appear here.',
            'no_sent_requests' => 'No sent requests',
            'sent_requests_description' => 'Project invitations you send to workers will appear here.',
            'no_received_requests' => 'No received requests',
            'received_requests_description' => 'Worker applications and invitations will appear here.',
            'no_join_requests' => 'No join requests',
            'join_requests_description' => 'Project applications submitted by your company will appear here.',
            'no_active_projects' => 'No active projects',
            'active_created_description' => 'Accepted workers for your projects will appear here.',
            'active_joined_description' => 'External projects your workers joined will appear here.',
            'no_completed_projects' => 'No completed projects',
            'completed_created_description' => 'Completed projects for your organization will appear here.',
            'completed_joined_description' => 'External projects completed by your workers will appear here.',
            'no_activity' => 'No project activity yet',
            'activity_description' => 'Requests, ongoing projects, and completed work will appear here.',
            'no_pending_activity' => 'No pending activity',
            'pending_activity_description' => 'Pending requests and projects awaiting acceptance will appear here.',
            'no_ongoing_activity' => 'No ongoing activity',
            'ongoing_activity_description' => 'Ongoing requests and projects will appear here.',
            'no_completed_activity' => 'No completed activity',
            'completed_activity_description' => 'Completed requests and projects will appear here.',
        ],

        'labels' => [
            'project' => 'Project',
            'requests' => 'Requests',
            'worker' => 'Worker',
            'assigned_workers' => 'Assigned Workers',
            'assignments' => 'Assignments',
            'worker_outcomes' => 'Worker Outcomes',
            'requested_worker' => 'Requested Worker',
            'proposed_worker' => 'Proposed Worker',
            'assigned_worker' => 'Assigned Worker',
            'requested_dates' => 'Requested Dates',
            'project_dates' => 'Project Dates',
            'worker_rate' => "Worker's Rate",
            'rate' => 'Rate',
            'final_rate' => 'Final Rate',
            'message' => 'Message',
            'message_sent' => 'Message Sent',
            'message_received' => 'Message Received',
            'application_message' => 'Application Message',
            'invitation_message' => 'Invitation Message',
            'company_name' => 'Company Name',
            'company' => 'Company',
            'company_owner' => 'Company Owner',
            'worker_offered' => 'Worker Offered',
            'reviewed_by' => 'Reviewed By',
            'requested_on' => 'Requested on',
            'accepted_on' => 'Accepted on',
            'rejected_on' => 'Rejected on',
            'cancelled_on' => 'Cancelled on',
            'completed_on' => 'Completed on',
            'original_request' => 'Original request',
            'rejection' => 'Rejection',
            'ended_on' => 'Ended on',
            'contact_company_owner' => 'Contact company owner',
            'worker_review' => 'Worker Review',
            'staffing_progress' => 'Staffing',
            'capacity' => ':committed / :required',
            'remaining' => ':count remaining',
            'recruiting' => 'Recruiting',
            'staffing_closed' => 'Staffing Closed',
            'invitation' => 'Invitation',
            'application' => 'Application',
            'incoming' => 'Incoming',
            'outgoing' => 'Outgoing',
        ],

        'actions' => [
            'complete_and_rate' => 'Complete & Rate',
            'complete_project' => 'Complete Project',
            'end_assignment' => 'End Assignment',
            'end_assignment_and_rate' => 'End Assignment & Rate',
            'view_worker_profile' => 'View Worker Profile',
            'view_request_history' => 'View request history',
            'view_project' => 'View Project',
            'stop_recruiting' => 'Stop Recruiting',
            'start_project' => 'Start Project',
        ],

        'details_modal' => [
            'load_error' => 'Unable to load project details. Please try again.',
        ],

        'worker_details_modal' => [
            'load_error' => 'Unable to load worker details. Please try again.',
        ],

        'roles' => [
            'administrator' => 'Administrator',
            'company_owner' => 'Company Owner',
            'planning_manager' => 'Planning Manager',
            'self_employed' => 'Self-employed',
        ],

        'states' => [
            'project_accepted' => 'Project accepted.',
            'project_completed' => 'Project completed successfully.',
            'assignment_completed' => 'Assignment completed successfully.',
            'assignment_ended_early' => 'Assignment ended early.',
        ],

        'success' => [
            'request_updated' => 'Request updated successfully.',
            'recruiting_closed' => 'Recruiting has been closed for this project.',
            'project_started' => 'Project has started successfully.',
            'assignment_completed' => 'Assignment completed successfully.',
            'assignment_ended_early' => 'Assignment ended early successfully.',
        ],

        'validation' => [
            'request_no_longer_pending' => 'This request is no longer pending.',
            'recruiting_closed' => 'This project is no longer accepting workers.',
            'project_not_eligible_for_staffing' => 'This project is not eligible for additional staffing.',
            'worker_already_committed' => 'This worker is already committed to this project.',
            'recruiting_already_closed' => 'Recruiting is already closed for this project.',
            'recruiting_requires_committed_worker' => 'At least one worker must be accepted before recruiting can be closed.',
            'project_not_ready_to_start' => 'This project is not ready to start.',
            'project_start_date_not_reached' => 'This project cannot start before its start date.',
            'project_start_requires_committed_worker' => 'At least one committed worker is required to start this project.',
            'project_not_in_progress' => 'This project is not currently in progress.',
            'assignment_not_ready_to_complete' => 'This assignment is not ready to be completed.',
            'assignment_not_ready_to_end_early' => 'This assignment is not ready to end early.',
            'project_end_date_not_reached' => 'This assignment cannot be completed before the project end date.',
            'rating_already_exists' => 'This worker has already been rated for this project.',
        ],

        'fallbacks' => [
            'no_feedback' => 'No feedback was provided for this worker.',
            'pending_activity' => 'Pending project activity.',
            'active_project' => 'Project currently active.',
        ],

        'rating' => [
            'score_given' => ':score/5 given',
            'score_received' => ':score/5 received',
        ],

        'response_modal' => [
            'accept_title' => 'Accept Request',
            'reject_title' => 'Reject Request',
            'accept_note' => ':contact will be notified when you confirm this request.',
            'reject_note' => ':contact will be notified when you confirm this rejection.',
            'acceptance_placeholder' => 'Add an optional message...',
            'rejection_placeholder' => 'Optional rejection reason...',
            'confirm_accept' => 'Confirm Accept',
            'confirm_reject' => 'Reject Request',
            'company_contact_fallback' => 'Team',
            'acceptance_message_self_employed' => "Hello :contact,\n\nYou've been accepted for the project “:project”.\n\nWe look forward to working with you. Thank you!",
            'acceptance_message_company' => "Hello :contact,\n\n:worker has been accepted for the project “:project”.\n\nWe look forward to working with you and your team. Thank you!",
            'rejection_message_self_employed' => "Hello :contact,\n\nUnfortunately, you have not been selected for the project “:project”.\n\nThank you for your interest.",
            'rejection_message_company' => "Hello :contact,\n\nUnfortunately, :worker has not been selected for the project “:project”.\n\nThank you for your interest.",
        ],

        'request_history_modal' => [
            'title' => 'Request History',
        ],

        'completion_modal' => [
            'title' => 'Complete & Rate Worker',
            'end_early_title' => 'End Assignment & Rate Worker',
            'rating_label' => 'How was your experience?',
            'comments_label' => 'Comments (optional)',
            'comments_placeholder' => 'Share your experience...',
        ],

        'recruiting_modal' => [
            'title' => 'Stop Recruiting',
            'message' => 'Stop recruiting for this project?',
            'subtitle' => 'Pending requests will be cancelled. Accepted workers will remain assigned.',
            'confirm' => 'Stop Recruiting',
        ],

        'start_modal' => [
            'title' => 'Start Project',
            'message' => 'Start this project now?',
            'subtitle' => 'Accepted worker assignments will move to in progress.',
            'confirm' => 'Start Project',
        ],
    ],

    //:: Settings Page
    'company_team' => [
        'title' => 'Company Team',
        'subtitle' => 'Manage planning managers for :company.',
        'add_planning_manager' => 'Add Planning Manager',
        'pending_invitations' => 'Pending Invitations',
        'pending' => 'Pending',
        'expires_at' => 'Expires :date',
        'cancel_invitation' => 'Cancel Invitation',
        'remove' => 'Remove',
        'cancel' => 'Cancel',
        'roles' => [
            'company_owner' => 'Company Owner',
            'planning_manager' => 'Planning Manager',
        ],
        'invite_modal' => [
            'title' => 'Invite a Planning Manager',
            'name' => 'Full Name',
            'email' => 'Email Address',
            'send' => 'Send Invitation',
        ],
        'cancel_invitation_modal' => [
            'title' => 'Cancel this invitation?',
            'message' => 'This invitation link will no longer be usable.',
        ],
        'remove_member_modal' => [
            'title' => 'Remove this planning manager?',
            'message' => 'This removes company access and deactivates the account. Historical records will be preserved.',
        ],
        'accept' => [
            'title' => 'Join the Company Team',
            'subtitle' => 'Set a password to join :company as a Planning Manager.',
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Confirm Password',
            'submit' => 'Accept Invitation',
        ],
        'email' => [
            'subject' => 'You are invited to join a company team',
            'heading' => 'You are invited to join :company',
            'introduction' => ':inviter invited you to join their company team.',
            'company' => 'Company',
            'inviter' => 'Invited by',
            'role' => 'Role',
            'accept' => 'Accept Invitation',
            'expires' => 'This invitation expires on :date.',
        ],
        'validation' => [
            'existing_user' => 'An account already exists with this email address.',
            'active_invitation' => 'An active invitation already exists for this email address.',
            'invalid_invitation' => 'This invitation is invalid, expired, cancelled, or already accepted.',
            'invalid_company_owner' => 'This company no longer has an eligible owner.',
        ],
        'success' => [
            'invitation_sent' => 'Planning Manager invitation sent.',
            'invitation_cancelled' => 'Planning Manager invitation cancelled.',
            'member_removed' => 'Planning Manager removed from the company.',
            'invitation_accepted' => 'Welcome to the company team.',
        ],
    ],

    'settings_page' => [
        'title' => 'Settings',

        'personal' => [
            'title' => 'Personal Information',
            'subtitle' => 'Update your personal details',
            'save_changes' => 'Save Changes',
            'name' => 'Full Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'company' => 'Company Name',
            'success' => 'Personal information updated.',
        ],

        'security' => [
            'title' => 'Password & Security',
            'subtitle' => 'Manage your password and security settings',
            'change_password' => 'Change Password',
            'hide' => 'Hide',
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'confirm_new_password' => 'Confirm New Password',
            'update_password' => 'Update Password',
            'password_updated' => 'Password updated.',
        ],

        'notifications' => [
            'title' => 'Notifications & Preferences',
            'subtitle' => 'Customize your notification settings',
            'email' => 'Email Notifications',
            'sms' => 'SMS Notifications',
            'projects' => 'Project Alerts',
            'language' => 'Language',
            'timezone' => 'Timezone',
            'save' => 'Save Preferences',
            'success' => 'Notification preferences updated.',
            'email_description' => 'Receive email updates',
            'sms_description' => 'Receive SMS updates',
            'projects_description' => 'Get notified about new projects',
            'timezone_options' => [
                'utc' => 'UTC',
                'newfoundland' => 'Newfoundland Time',
                'atlantic' => 'Atlantic Time',
                'eastern' => 'Eastern Time',
                'central' => 'Central Time',
                'saskatchewan' => 'Saskatchewan Time',
                'mountain' => 'Mountain Time',
                'pacific' => 'Pacific Time',
            ],
        ],

        'danger_zone' => [
            'title' => 'Danger Zone',
            'subtitle' => 'Deactivate access to your account',
            'deactivate_account' => 'Deactivate Account',
            'deactivate_modal_title' => 'Deactivate your account?',
            'deactivate_modal_description' => 'Enter your password to deactivate your account. You will be signed out on all devices.',
            'password' => 'Password',
            'owner_cannot_deactivate' => 'Company owners cannot deactivate their account while they own a company.',
        ],

        'common' => [
            'languages' => [
                'en' => 'English',
                'fr' => 'French',
                'es' => 'Spanish',
            ],
        ],
    ],

    'emails' => [
        'common' => [
            'project_details' => 'Project Details',
            'title' => 'Title',
            'message' => 'Message',
            'worker' => 'Worker',
            'role' => 'Role',
            'name' => 'Name',
            'company' => 'Company',
            'view_request' => 'View Request',
        ],
        'project_request' => [
            'subject' => 'New Project Join Request',
            'heading' => 'New Request to Join Project',
            'company_intro' => ':company has offered a worker for your project.',
            'self_employed_intro' => 'A self-employed worker has applied to your project.',
            'worker_information' => 'Worker Information',
            'employment' => 'Employment',
            'lending_company' => 'Lending Company',
            'login_instruction' => 'Please log in to review this request.',
        ],
        'worker_request' => [
            'subject' => 'New Worker Request',
            'company_heading' => 'New Request for Your Worker',
            'self_employed_heading' => 'You’ve Been Invited to a Project',
            'company_intro' => ':company has requested one of your workers for a project.',
            'self_employed_intro' => ':company has invited you to join a project.',
            'worker_details' => 'Worker Details',
            'self_employed_notice' => 'This request is specifically for you.',
            'requesting_company' => 'Requesting Company',
            'login_instruction' => 'Please log in to your account to accept or decline this request.',
        ],
    ],
];
