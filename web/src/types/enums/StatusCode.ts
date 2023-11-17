export enum StatusCode {
    OK = 200,
    Created = 201,
    BadRequest = 400,
    UnprocessableEntity = 422,
    Locked = 423,
    Unauthorized,
    PaymentRequired,
    Forbidden,
    NotFound,
}