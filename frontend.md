{
  "primary_color": "#2563EB",
  "secondary_color": "#10B981",
  "background_color": "#F9FAFB",
  "text_primary": "#111827",
  "text_secondary": "#6B7280",
  "error_color": "#EF4444",
  "success_color": "#22C55E",
  "warning_color": "#F59E0B"
}
{
  "mobile_first": true,
  "breakpoints": {
    "mobile": "0-640px",
    "tablet": "641-1024px",
    "desktop": "1025px+"
  },
  "grid_system": {
    "mobile": "1 column",
    "tablet": "2 columns",
    "desktop": "3-4 columns"
  }
}


🧩 🔥 COMPLETE UI FLOW (PAGES)
{
  "pages": [

    {
      "page": "homepage",
      "components": [
        "top_bar (Find rooms without brokers)",
        "header (logo + city selector + login + post button)",
        "search_bar (city + area + budget)",
        "filters (rent, type, availability)",
        "room_cards (image + rent + location + trust badge)",
        "pagination"
      ],
      "layout": "grid cards",
      "goal": "show nearby rooms quickly"
    },

    {
      "page": "listing_details",
      "components": [
        "image_gallery",
        "room_details",
        "rent_info",
        "location_map_preview",
        "trust_score_badge",
        "view_contact_button"
      ],
      "goal": "convince user to contact"
    },

    {
      "page": "contact_unlock_popup",
      "components": [
        "donation_options (₹20, ₹50, skip)",
        "register_form (phone + OTP)",
        "unlock_button"
      ],
      "goal": "convert user to registered + optional donation"
    },

    {
      "page": "post_room",
      "components": [
        "form (title, rent, type, description)",
        "location_auto_fill",
        "map_pin (optional)",
        "image_upload",
        "submit_button"
      ],
      "goal": "easy listing creation"
    },

    {
      "page": "user_dashboard",
      "components": [
        "my_listings",
        "favorites",
        "contact_unlocks",
        "edit/delete buttons"
      ],
      "goal": "user control"
    },

    {
      "page": "auth_pages",
      "components": [
        "phone_input",
        "otp_verification",
        "error_messages"
      ],
      "goal": "secure login"
    }

  ]
}
🧠 🔥 UX FLOW (USER JOURNEY)
{
  "steps": [
    "User opens website",
    "Location auto-detected",
    "Rooms displayed",
    "User filters/searches",
    "User clicks listing",
    "User clicks view contact",
    "Donation popup appears",
    "User registers via OTP",
    "Contact unlocked"
  ]
}
⚠️ 🔥 ERROR HANDLING SYSTEM
{
  "form_validation": {
    "title": "Minimum 10 characters required",
    "description": "Minimum 30 characters required",
    "images": "At least 2 images required",
    "phone": "Invalid phone number",
    "email": "Invalid email format"
  },

  "api_errors": {
    "401": "Unauthorized access",
    "403": "Access denied",
    "404": "Data not found",
    "500": "Server error, try again later"
  },

  "user_feedback": {
    "success_message": "Action completed successfully",
    "error_message": "Something went wrong",
    "loading_state": "Please wait..."
  },

  "edge_cases": [
    "No listings found → show empty state UI",
    "Location not detected → ask manual city selection",
    "Duplicate listing → show warning",
    "Too many requests → show rate limit message"
  ]
}
⚡ 🔥 PERFORMANCE UX RULES
{
  "lazy_loading": true,
  "image_optimization": true,
  "pagination": 20,
  "cache_usage": "Redis for homepage and filters",
  "skeleton_loader": true
}
🎯 🔥 CODEX FINAL PROMPT (MOST IMPORTANT)

👉 Ye direct Codex ko do:

{
  "task": "Build complete frontend UI for a room listing platform",

  "requirements": {
    "design": "clean, modern, mobile-first UI",
    "color_theme": "blue + green trust based",
    "responsive": true,
    "pages": [
      "homepage",
      "listing_details",
      "post_room",
      "user_dashboard",
      "auth_pages"
    ],
    "components": [
      "search bar",
      "filters",
      "room cards",
      "image gallery",
      "OTP login",
      "donation popup"
    ]
  },

  "logic": {
    "auto_location": true,
    "contact_unlock": "requires OTP + optional donation",
    "listing_validation": true,
    "spam_protection": true
  },

  "error_handling": {
    "show_validation_errors": true,
    "api_error_messages": true,
    "loading_states": true
  },

  "performance": {
    "use_lazy_loading": true,
    "optimize_images": true,
    "use_pagination": true
  }
}

{
  "page": "user_dashboard",

  "layout": {
    "type": "responsive",
    "structure": {
      "desktop": "sidebar + main content",
      "mobile": "top tabs + stacked layout"
    }
  },

  "theme": {
    "primary": "#2563EB",
    "secondary": "#10B981",
    "background": "#F9FAFB",
    "card": "#FFFFFF",
    "text": "#111827"
  },

  "components": [

    {
      "name": "sidebar",
      "position": "left (desktop) / hidden (mobile)",
      "items": [
        "Dashboard Overview",
        "My Listings",
        "Favorites",
        "Contact Unlocks",
        "Profile Settings",
        "Logout"
      ]
    },

    {
      "name": "header_bar",
      "items": [
        "user_name",
        "location_selector",
        "notification_icon"
      ]
    },

    {
      "name": "dashboard_stats",
      "type": "cards",
      "items": [
        "total_listings",
        "active_listings",
        "total_views",
        "contacts_unlocked"
      ]
    },

    {
      "name": "my_listings",
      "type": "card_list",
      "features": [
        "image_preview",
        "rent_display",
        "location",
        "status_badge (active/filled/expired)",
        "trust_score_badge",
        "edit_button",
        "delete_button",
        "mark_as_filled_button"
      ]
    },

    {
      "name": "favorites",
      "type": "card_list",
      "features": [
        "saved_room_cards",
        "remove_from_favorites"
      ]
    },

    {
      "name": "contact_unlocks",
      "type": "list",
      "features": [
        "room_title",
        "contact_number",
        "unlock_date"
      ]
    },

    {
      "name": "profile_settings",
      "type": "form",
      "fields": [
        "name",
        "phone",
        "email"
      ],
      "features": [
        "edit_profile",
        "verify_email",
        "verify_phone"
      ]
    }

  ]
}🧩 🔥 USER DASHBOARD UI JSON (FINAL)
{
  "page": "user_dashboard",

  "layout": {
    "type": "responsive",
    "structure": {
      "desktop": "sidebar + main content",
      "mobile": "top tabs + stacked layout"
    }
  },

  "theme": {
    "primary": "#2563EB",
    "secondary": "#10B981",
    "background": "#F9FAFB",
    "card": "#FFFFFF",
    "text": "#111827"
  },

  "components": [

    {
      "name": "sidebar",
      "position": "left (desktop) / hidden (mobile)",
      "items": [
        "Dashboard Overview",
        "My Listings",
        "Favorites",
        "Contact Unlocks",
        "Profile Settings",
        "Logout"
      ]
    },

    {
      "name": "header_bar",
      "items": [
        "user_name",
        "location_selector",
        "notification_icon"
      ]
    },

    {
      "name": "dashboard_stats",
      "type": "cards",
      "items": [
        "total_listings",
        "active_listings",
        "total_views",
        "contacts_unlocked"
      ]
    },

    {
      "name": "my_listings",
      "type": "card_list",
      "features": [
        "image_preview",
        "rent_display",
        "location",
        "status_badge (active/filled/expired)",
        "trust_score_badge",
        "edit_button",
        "delete_button",
        "mark_as_filled_button"
      ]
    },

    {
      "name": "favorites",
      "type": "card_list",
      "features": [
        "saved_room_cards",
        "remove_from_favorites"
      ]
    },

    {
      "name": "contact_unlocks",
      "type": "list",
      "features": [
        "room_title",
        "contact_number",
        "unlock_date"
      ]
    },

    {
      "name": "profile_settings",
      "type": "form",
      "fields": [
        "name",
        "phone",
        "email"
      ],
      "features": [
        "edit_profile",
        "verify_email",
        "verify_phone"
      ]
    }

  ]
}
🧠 🔥 UX FLOW (Dashboard Use)
{
  "user_actions": [
    "User logs in",
    "Dashboard overview loads",
    "User sees stats",
    "User clicks My Listings",
    "User edits or deletes listing",
    "User marks listing as filled",
    "User checks contact unlock history",
    "User manages favorites"
  ]
}
📱 🔥 MOBILE UX (VERY IMPORTANT)
{
  "layout": {
    "type": "tab_based",
    "tabs": [
      "Home",
      "Listings",
      "Favorites",
      "Profile"
    ]
  },
  "rules": [
    "no sidebar",
    "big buttons",
    "scroll-based navigation"
  ]
}
⚠️ 🔥 ERROR HANDLING (Dashboard)
{
  "cases": [
    {
      "scenario": "no listings",
      "ui": "show empty state with 'Post Room' button"
    },
    {
      "scenario": "network error",
      "ui": "show retry button + message"
    },
    {
      "scenario": "delete failed",
      "ui": "show error toast"
    },
    {
      "scenario": "invalid input",
      "ui": "inline validation message"
    }
  ]
}
⚡ 🔥 PERFORMANCE UX
{
  "features": [
    "lazy loading for listings",
    "pagination (10 per page)",
    "skeleton loader for loading state",
    "cache dashboard data for 60 seconds"
  ]
}
🎨 🔥 DESIGN STYLE (LUXURY FEEL)
{
  "style": [
    "rounded cards",
    "soft shadows",
    "minimal icons",
    "clean typography",
    "white space focused"
  ]
}
🚀 🔥 CODEX PROMPT (FINAL)

👉 Ye direct Codex ko de do:

{
  "task": "Build a user dashboard UI for room listing platform",

  "requirements": {
    "design": "modern, clean, mobile-first",
    "layout": "responsive with sidebar (desktop) and tabs (mobile)",
    "theme": "blue and green trust-based colors",
    "components": [
      "dashboard stats cards",
      "listing management cards",
      "favorites list",
      "contact unlock history",
      "profile settings"
    ]
  },

  "features": {
    "listing_management": [
      "edit listing",
      "delete listing",
      "mark as filled"
    ],
    "user_control": [
      "view saved listings",
      "view unlocked contacts",
      "edit profile"
    ]
  },

  "error_handling": {
    "show_empty_states": true,
    "show_validation_errors": true,
    "show_toast_notifications": true
  },

  "performance": {
    "use_lazy_loading": true,
    "use_pagination": true,
    "use_cache": true
  }
}