Helps to know immediately when something important happens: app sends FCM push notification to your phone. You can open it and see brief details. 

Examples:
Laravel exception
Cron failed
Queue failed
Payment failed
Mobile app crash
API down
Server disk full
New order
Stripe webhook failed
Deployment completed

## NotifyHub
POST /events

{
"title": "Payment Failed",
"message": "Order #1234",
"severity": "critical",
"application": "URL or name of the app",
"context": "TBD"
}
Store event.
Push to mobile devices.

All applications send events to the notification server:
Stores event
Sends push notification
Mobile app receives it
User can open details

Tables:
users
user_devices (FCM tokens)
projects
channels
events

Mobile app:
Projects
Feed
Event Details
Settings

The user installs ONE mobile app and grants push permission once. Only the notification platform sends pushes.So the user doesn't need 15 apps installed.