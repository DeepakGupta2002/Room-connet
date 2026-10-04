{
  "project": "No Broker Room Platform",

  "core_logic": {
    "idea": "Tenant lists room before leaving, seeker finds and contacts directly without broker",
    "main_modules": [
      "listing_creation",
      "search_discovery",
      "contact_unlock",
      "trust_validation",
      "spam_control"
    ]
  },

  "schema_usage": {

    "users": {
      "use_case": [
        "user registration via phone OTP",
        "store verified users",
        "identify listing owner",
        "track user activity"
      ]
    },

    "posts": {
      "use_case": [
        "main room listing data",
        "store rent, location, contact info",
        "control visibility (active, expired, filled)",
        "apply trust score and flagging"
      ],
      "connected_with": [
        "users",
        "post_images",
        "reports",
        "donations",
        "contact_unlocks"
      ]
    },

    "post_images": {
      "use_case": [
        "store multiple images of room",
        "used in listing display page"
      ]
    },

    "contact_unlocks": {
      "use_case": [
        "track which user unlocked contact",
        "avoid showing popup again",
        "control access to phone number"
      ]
    },

    "donations": {
      "use_case": [
        "store optional payment before contact unlock",
        "track user contribution"
      ]
    },

    "reports": {
      "use_case": [
        "user can report fake or duplicate listing",
        "if reports >= 3 then mark post as flagged"
      ]
    },

    "favorites": {
      "use_case": [
        "user saves listings",
        "used in dashboard"
      ]
    },

    "login_logs": {
      "use_case": [
        "track login attempts",
        "detect suspicious activity"
      ]
    },

    "blocked_ips": {
      "use_case": [
        "block spam users and bots"
      ]
    },

    "rate_limits": {
      "use_case": [
        "limit API usage",
        "prevent spam actions like multiple posts or login attempts"
      ]
    },

    "activity_logs": {
      "use_case": [
        "track user actions like create/edit/delete post",
        "debug and fraud detection"
      ]
    }
  },

  "system_flows": {

    "listing_creation": {
      "steps": [
        "User clicks Post Room",
        "System auto-detects location (lat/lng)",
        "Auto-fill city, state, area",
        "User fills form (rent, description, images)",
        "Validate data (min images, valid date, verified user)",
        "Save data in posts table",
        "Save images in post_images",
        "Calculate trust_score",
        "Set status = active"
      ]
    },

    "search_discovery": {
      "steps": [
        "User opens website",
        "System detects location or asks for city",
        "Fetch posts where city matches",
        "Apply filters (rent, type, availability)",
        "Apply conditions (active, not flagged, valid date)",
        "Return results with pagination",
        "Cache results using Redis"
      ]
    },

    "listing_view": {
      "steps": [
        "User opens listing details",
        "Fetch post + images",
        "Show rent, location, description",
        "Show trust score"
      ]
    },

    "contact_unlock": {
      "steps": [
        "User clicks View Contact",
        "Check if already unlocked in contact_unlocks",
        "If not unlocked → show donation popup",
        "User can donate or skip",
        "If not logged in → ask for OTP login",
        "After login → unlock contact",
        "Save in contact_unlocks",
        "Show contact number"
      ]
    },

    "spam_control": {
      "steps": [
        "User reports listing",
        "Save in reports table",
        "If reports >= 3 → update posts.is_flagged = 1",
        "Hide flagged listings from search"
      ]
    },

    "auto_expiry": {
      "steps": [
        "Run cron job daily",
        "Check posts where leaving_date < today",
        "Update status = expired"
      ]
    }
  },

  "validation_rules": {
    "listing": [
      "minimum 2 images required",
      "leaving_date must be future",
      "phone must be verified",
      "title and description length validation"
    ],
    "user": [
      "OTP verification required",
      "rate limit login attempts"
    ]
  },

  "performance_logic": {
    "cache": "Use Redis for homepage and search results",
    "pagination": "limit 20 per page",
    "indexes": [
      "city",
      "area",
      "status",
      "leaving_date",
      "trust_score"
    ]
  },

  "security_logic": {
    "rate_limit": "limit API requests per user/IP",
    "blocked_ips": "block malicious traffic",
    "file_upload": "allow only images with size limit",
    "auth": "JWT or session-based authentication"
  }
}

isme main ye soch raha hi ki jab bhi koi kiraya dar list karega room ki ye khali hone wala hai is date se 
to jo kiraydar hoga oh number jo add hoga jo kisi user ko dikhega oh kirayedar ka number nahi hoga oh room owner ka number hoga 
