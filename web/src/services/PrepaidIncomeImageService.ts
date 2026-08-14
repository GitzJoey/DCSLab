import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { AxiosResponse, AxiosError, isAxiosError } from 'axios';
import { ServiceResponse } from '../types/services/ServiceResponse';
import ErrorHandlerService from './ErrorHandlerService';
import { Resource } from '../types/resources/Resource';
import { PrepaidIncomeImage } from '../types/models/PrepaidIncomeImage';

export default class PrepaidIncomeImageService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async upload(file: File): Promise<ServiceResponse<PrepaidIncomeImage | null>> {
    const result: ServiceResponse<PrepaidIncomeImage | null> = {
      success: false,
    };

    try {
      const formData = new FormData();
      formData.append('image', file);

      const url = route('api.post.prepaid_income.image.upload', undefined, false, this.ziggyRoute);

      axios.defaults.headers.common['Content-Type'] = 'multipart/form-data';

      const response: AxiosResponse<Resource<PrepaidIncomeImage>> = await axios.post(url, formData);

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
