import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { AxiosResponse, AxiosError, isAxiosError } from 'axios';
import { ServiceResponse } from '../types/services/ServiceResponse';
import ErrorHandlerService from './ErrorHandlerService';
import { Resource } from '../types/resources/Resource';
import { PrepaidExpenseImage } from '../types/models/PrepaidExpenseImage';

export default class PrepaidExpenseImageService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async upload(file: File): Promise<ServiceResponse<PrepaidExpenseImage | null>> {
    const result: ServiceResponse<PrepaidExpenseImage | null> = {
      success: false,
    };

    try {
      const formData = new FormData();
      formData.append('image', file);

      const url = route('api.post.prepaid_expense.image.upload', undefined, false, this.ziggyRoute);

      axios.defaults.headers.common['Content-Type'] = 'multipart/form-data';

      const response: AxiosResponse<Resource<PrepaidExpenseImage>> = await axios.post(url, formData);

      result.success = true;
      result.data = response.data.data;

      return result;
    } catch (e: unknown) {
      if (e instanceof Error && e.message.includes('Ziggy error')) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
      } else {
        return result;
      }
    }
  }
}
