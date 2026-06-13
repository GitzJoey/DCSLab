export enum ReasonPhrases {
  //Standard
  OK = 'OK',
  CREATED = 'Created',
  ACCEPTED = 'Accepted',
  BAD_REQUEST = 'Bad Request',
  UNAUTHORIZED = 'Unauthorized',
  FORBIDDEN = 'Forbidden',
  NOT_FOUND = 'Not Found',
  METHOD_NOT_ALLOWED = 'Method Not Allowed',
  REQUEST_TIMEOUT = 'Request Timeout',
  UNPROCESSABLE_ENTITY = 'Unprocessable Entity',
  INTERNAL_SERVER_ERROR = 'Internal Server Error',
  BAD_GATEWAY = 'Bad Gateway',
  NO_CONTENT = 'No Content',
  SERVICE_UNAVAILABLE = 'Service Unavailable',

  //Non Standard
  //419 Unknown Status (CSRF Token Missing / Expired)
  LARAVEL_UNKNOWN_STATUS = 'Unknown Status',
}
