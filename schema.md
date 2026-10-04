{
  "project": "No Broker Room Finder Platform",
  "goal": "Allow tenants to list rooms and seekers to find and contact directly without brokers. Ensure trust, validation, and scalability across India.",
  
  "database_schema": {
    
    "users": {
      "fields": [
        "id",
        "name",
        "phone",
        "email",
        "phone_verified_at",
        "email_verified_at",
        "status",
        "created_at",
        "updated_at"
      ],
      "purpose": "Store user identity and verification status",
      "relations": {
        "hasMany": ["posts", "donations", "reports", "favorites", "login_logs"]
      }
    },

    "posts": {
      "fields": [
        "id",
        "user_id",
        "title",
        "description",
        "rent_amount",
        "rent_type",
        "security_deposit",
        "maintenance_charge",
        "room_type",
        "contact_name",
        "contact_phone",
        "leaving_date",
        "available_from",
        "status",
        "state",
        "city",
        "area",
        "address",
        "latitude",
        "longitude",
        "trust_score",
        "is_flagged",
        "slug",
        "created_at",
        "updated_at"
      ],
      "purpose": "Main listing table for rooms",
      "logic": [
        "Only show posts where status = active",
        "Hide posts where is_flagged = 1",
        "Hide posts where leaving_date < today",
        "Sort by trust_score DESC and latest"
      ],
      "relations": {
        "belongsTo": ["users"],
        "hasMany": ["post_images", "reports", "donations", "contact_unlocks"]
      }
    },

    "post_images": {
      "fields": [
        "id",
        "post_id",
        "image_path",
        "created_at"
      ],
      "purpose": "Store room images",
      "relations": {
        "belongsTo": ["posts"]
      }
    },

    "donations": {
      "fields": [
        "id",
        "user_id",
        "post_id",
        "amount",
        "status",
        "created_at"
      ],
      "purpose": "Store optional donation before contact unlock",
      "relations": {
        "belongsTo": ["users", "posts"]
      }
    },

    "contact_unlocks": {
      "fields": [
        "id",
        "user_id",
        "post_id",
        "unlocked_at"
      ],
      "purpose": "Track which user has unlocked which contact",
      "logic": [
        "Once unlocked, do not show popup again",
        "Allow unlock even if donation is skipped"
      ],
      "relations": {
        "belongsTo": ["users", "posts"]
      }
    },

    "reports": {
      "fields": [
        "id",
        "post_id",
        "user_id",
        "reason",
        "created_at"
      ],
      "purpose": "Handle fake or spam listings",
      "logic": [
        "If reports >= 3, set is_flagged = 1",
        "Flagged posts should not be visible"
      ],
      "relations": {
        "belongsTo": ["users", "posts"]
      }
    },

    "favorites": {
      "fields": [
        "id",
        "user_id",
        "post_id",
        "created_at"
      ],
      "purpose": "Allow users to save listings",
      "relations": {
        "belongsTo": ["users", "posts"]
      }
    },

    "login_logs": {
      "fields": [
        "id",
        "user_id",
        "ip_address",
        "device",
        "status",
        "created_at"
      ],
      "purpose": "Track login activity and detect suspicious access"
    },

    "blocked_ips": {
      "fields": [
        "id",
        "ip_address",
        "reason",
        "blocked_until"
      ],
      "purpose": "Block malicious users or bots"
    },

    "rate_limits": {
      "fields": [
        "id",
        "user_or_ip",
        "action",
        "count",
        "last_request"
      ],
      "purpose": "Prevent API abuse and spam",
      "logic": [
        "Limit actions like login, post creation, contact view"
      ]
    },

    "activity_logs": {
      "fields": [
        "id",
        "user_id",
        "action",
        "meta_data",
        "created_at"
      ],
      "purpose": "Track user activity for debugging and fraud detection"
    }
  },

  "core_flows": {
    "listing_creation": [
      "User submits room details",
      "Validate input (images, date, location)",
      "Verify user (phone/email)",
      "Calculate trust score",
      "Save post"
    ],

    "search_flow": [
      "Detect user location",
      "Filter by city/area",
      "Apply conditions (active, not flagged, valid date)",
      "Sort results"
    ],

    "contact_unlock": [
      "User clicks view contact",
      "Show donation popup",
      "Ask for registration (OTP)",
      "Unlock contact",
      "Save in contact_unlocks"
    ],

    "spam_control": [
      "Users can report listing",
      "If reports >= 3, hide listing",
      "Limit user to 2 posts per day"
    ]
  },

  "performance": {
    "cache": "Use Redis for listing and search caching",
    "indexes": [
      "city",
      "area",
      "status",
      "leaving_date",
      "trust_score"
    ],
    "pagination": "Limit results to 20 per request"
  }
}