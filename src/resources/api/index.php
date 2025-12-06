<?php
// CONFIG CONSTANTS HERE
require_once __DIR__ . '/helpers.php';

// Instantiate base classes
$handler = new RequestHandler();
$router = new Router();

// Base sets
$baseSets = [
    'posts' => 'posts',
    'comments' => 'comments',
    'tags' => 'tags',
    'users' => 'users',
];

// -------------------------------------------------------------
// MOCK DATA (REPLIT FRIENDLY)
// -------------------------------------------------------------

$mockPosts = [
    [
        "id" => 1,
        "title" => "Welcome to the Blog",
        "slug" => "welcome-to-the-blog",
        "content" => "This is our first post!",
        "tags" => ["intro", "welcome"],
        "created_at" => "2025-01-01",
        "user_id" => 1
    ],
    [
        "id" => 2,
        "title" => "Second Post",
        "slug" => "second-post",
        "content" => "Another example post for testing.",
        "tags" => ["general"],
        "created_at" => "2025-01-10",
        "user_id" => 1
    ]
];

$mockTags = [
    ["id" => 1, "name" => "intro"],
    ["id" => 2, "name" => "welcome"],
    ["id" => 3, "name" => "general"]
];

$mockComments = [
    ["id" => 1, "post_id" => 1, "message" => "Nice post!", "user_id" => 2],
    ["id" => 2, "post_id" => 1, "message" => "Great work!", "user_id" => 3]
];

$mockUsers = [
    ["id" => 1, "name" => "Admin", "role" => "admin"],
    ["id" => 2, "name" => "Jane Doe", "role" => "user"],
    ["id" => 3, "name" => "John Smith", "role" => "user"]
];

// -------------------------------------------------------------
// Helper function to simulate database lookups
// -------------------------------------------------------------
function findBySlug($list, $slug)
{
    foreach ($list as $item) {
        if ($item["slug"] === $slug) {
            return $item;
        }
    }
    return null;
}

// -------------------------------------------------------------
// GET ROUTES
// -------------------------------------------------------------

// Home route
$router->get('/', function () use ($handler) {
    $handler->respond(["message" => "Blog API Running"]);
});

// Get all posts
$router->get('/posts', function () use ($handler, $mockPosts) {
    $handler->respond($mockPosts);
});

// Get a single post by slug
$router->get('/posts/:slug', function ($params) use ($handler, $mockPosts, $mockComments) {
    $post = findBySlug($mockPosts, $params['slug']);

    if (!$post) {
        $handler->respond(["error" => "Post not found"], 404);
        return;
    }

    $comments = array_filter($mockComments, function ($c) use ($post) {
        return $c["post_id"] == $post["id"];
    });

    $post["comments"] = array_values($comments);

    $handler->respond($post);
});

// Get tags
$router->get('/tags', function () use ($handler, $mockTags) {
    $handler->respond($mockTags);
});

// Get all comments
$router->get('/comments', function () use ($handler, $mockComments) {
    $handler->respond($mockComments);
});

// Get users
$router->get('/users', function () use ($handler, $mockUsers) {
    $handler->respond($mockUsers);
});

// -------------------------------------------------------------
// POST ROUTES
// -------------------------------------------------------------

// Create a comment
$router->post('/comments', function () use ($handler, &$mockComments) {
    $data = $handler->getJsonData();

    if (!isset($data["post_id"]) || !isset($data["message"])) {
        $handler->respond(["error" => "Invalid comment data"], 400);
        return;
    }

    $newComment = [
        "id" => count($mockComments) + 1,
        "post_id" => $data["post_id"],
        "message" => $data["message"],
        "user_id" => $data["user_id"] ?? null
    ];

    $mockComments[] = $newComment;

    $handler->respond($newComment, 201);
});

// Create a post
$router->post('/posts', function () use ($handler, &$mockPosts) {
    $data = $handler->getJsonData();

    if (!isset($data["title"]) || !isset($data["content"])) {
        $handler->respond(["error" => "Invalid post data"], 400);
        return;
    }

    $slug = strtolower(str_replace(" ", "-", $data["title"]));

    $newPost = [
        "id" => count($mockPosts) + 1,
        "title" => $data["title"],
        "slug" => $slug,
        "content" => $data["content"],
        "tags" => $data["tags"] ?? [],
        "created_at" => date("Y-m-d"),
        "user_id" => $data["user_id"] ?? 1
    ];

    $mockPosts[] = $newPost;

    $handler->respond($newPost, 201);
});

// -------------------------------------------------------------
// PUT ROUTES
// -------------------------------------------------------------

// Update a post
$router->put('/posts/:slug', function ($params) use ($handler, &$mockPosts) {
    foreach ($mockPosts as &$post) {
        if ($post["slug"] === $params["slug"]) {
            $data = $handler->getJsonData();

            foreach ($data as $key => $value) {
                $post[$key] = $value;
            }

            $handler->respond($post);
            return;
        }
    }

    $handler->respond(["error" => "Post not found"], 404);
});

// -------------------------------------------------------------
– DELETE ROUTES
// -------------------------------------------------------------

$router->delete('/posts/:slug', function ($params) use ($handler, &$mockPosts) {
    foreach ($mockPosts as $i => $post) {
        if ($post["slug"] === $params["slug"]) {
            unset($mockPosts[$i]);
            $handler->respond(["message" => "Post deleted"]);
            return;
        }
    }

    $handler->respond(["error" => "Post not found"], 404);
});

// DELETE comment
$router->delete('/comments/:id', function ($params) use ($handler, &$mockComments) {
    foreach ($mockComments as $i => $comment) {
        if ($comment["id"] == $params["id"]) {
            unset($mockComments[$i]);
            $handler->respond(["message" => "Comment deleted"]);
            return;
        }
    }

    $handler->respond(["error" => "Comment not found"], 404);
});

// -------------------------------------------------------------
// RUN ROUTER
// -------------------------------------------------------------
$router->run();
?>
