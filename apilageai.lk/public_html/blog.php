<?php
require_once __DIR__ . '/../backend/bootstrap.php';

page_header(
    "ApilageAI Blog | Sri Lanka's #1 Student AI Insights, Guides, and Updates",
    "Explore ApilageAI's SEO-focused blog: Sri Lankan syllabus learning, AI study tips, MCQ practice, mind maps, and student success stories."
);

if (isset($smarty)) {
    $posts = [
        [
            'title' => "Why ApilageAI is Sri Lanka's #1 AI for Students",
            'slug' => 'sri-lanka-number-one-student-ai',
            'summary' => 'Discover why students across Sri Lanka choose ApilageAI for syllabus-aligned learning, native-language support, and daily study success.',
            'tags' => ['Sri Lanka', 'Students', 'AI', 'Syllabus', 'Learning']
        ],
        [
            'title' => 'How to Use AI for O/L and A/L Exam Preparation',
            'slug' => 'ai-for-ol-al-exam-prep',
            'summary' => 'Step-by-step strategies to study smarter with ApilageAI: MCQ practice, summaries, and exam-focused explanations.',
            'tags' => ['O/L', 'A/L', 'Exam', 'Study']
        ],
        [
            'title' => 'Mind Maps and Visual Graphs: Learn Faster in Sri Lanka',
            'slug' => 'mindmaps-visual-graphs-student-guide',
            'summary' => 'Turn lessons into visual memory maps with ApilageAI mind maps and graphs designed for students.',
            'tags' => ['Mindmaps', 'Visual Graphs', 'Study Tips']
        ],
        [
            'title' => 'MCQ Game Mode: Practice Sri Lankan Syllabus the Fun Way',
            'slug' => 'mcq-game-sri-lankan-syllabus',
            'summary' => 'ApilageAI turns MCQ practice into a fast and effective game mode for exam preparation.',
            'tags' => ['MCQ', 'Games', 'Syllabus']
        ],
        [
            'title' => 'Daily Life and Study Balance with ApilageAI',
            'slug' => 'daily-life-study-balance-ai',
            'summary' => 'Plan routines, track learning streaks, and stay focused with AI guidance built for students.',
            'tags' => ['Daily Life', 'Productivity', 'Streaks']
        ],
        [
            'title' => 'Public Profiles and Learning Streaks: Share Your Growth',
            'slug' => 'public-profiles-learning-streaks',
            'summary' => 'Showcase your best chats, track streaks, and inspire other Sri Lankan learners with ApilageAI profiles.',
            'tags' => ['Public Profiles', 'Learning Streaks', 'Community']
        ],
        [
            'title' => 'AI Image Generation for Student Projects in Sri Lanka',
            'slug' => 'ai-image-generation-student-projects',
            'summary' => 'Create visuals for school projects, presentations, and creative work with ApilageAI image tools.',
            'tags' => ['Images', 'Projects', 'Creativity']
        ],
        [
            'title' => 'Sri Lankan Data Sources That Power ApilageAI',
            'slug' => 'data-sources-sri-lanka-education',
            'summary' => 'Learn how ApilageAI uses government and education sources to deliver accurate answers.',
            'tags' => ['Data Sources', 'Education', 'Sri Lanka']
        ],
        [
            'title' => 'Free vs Pro: LKR Pay-As-You-Go AI for Students',
            'slug' => 'free-vs-pro-lkr-student-ai',
            'summary' => 'Compare free credits with Pro pay-as-you-go pricing in LKR using a secure Sri Lankan gateway.',
            'tags' => ['Pricing', 'LKR', 'Pro']
        ],
        [
            'title' => 'How ApilageAI Supports Sinhala, Tamil, and English',
            'slug' => 'trilingual-learning-sri-lanka',
            'summary' => 'ApilageAI helps students learn in Sinhala, Tamil, or English with clear, local explanations.',
            'tags' => ['Sinhala', 'Tamil', 'English', 'Languages']
        ]
    ];

    shuffle($posts);
    $smarty->assign('blog_posts', $posts);
}

page_footer('blog');
?>
